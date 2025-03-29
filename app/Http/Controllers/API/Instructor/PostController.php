<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Posts\StorePostRequest;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Models\Approvable;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\PostSubmittedForApprovalNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, ApiResponseTrait;

    const FOLDER = 'blogs';

    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $posts = Post::query()->with([
                'user',
                'category',
            ])
                ->where('user_id', $user->id)
                ->get();

            if ($posts->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy bài viết nào');
            }

            return $this->respondOk('Danh sách bài viết của:' . $posts->first()->user->name, $posts);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function store(StorePostRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->except('thumbnail', 'published_at');

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), self::FOLDER);
            }

            $data['user_id'] = Auth::id();

            $data['category_id'] = $request->input('category_id');

            $data['status'] = 'pending';

            $data['slug'] = !empty($data['title'])
                ? Str::slug($data['title']) . '-' . Str::uuid()
                : Str::uuid();

            $post = Post::query()->create($data);


            if (!empty($request->input('tags'))) {
                $tags = collect($request->input('tags'))->map(function ($tagName) {
                    return Tag::query()->firstOrCreate([
                        'name' => $tagName,
                        'slug' => Str::slug($tagName) ?? Str::uuid()
                    ]);
                });

                $post->tags()->sync($tags->pluck('id'));
            }
            // Tạo yêu cầu kiểm duyệt
            $approvable = Approvable::query()->create([
                'approver_id' => null,
                'status' => 'pending',
                'note' => null,
                'reason' => null,
                'content_modification' => 0,
                'approvable_type' => Post::class,
                'approvable_id' => $post->id,
                'request_date' => now(),
                'approved_at' => null,
                'rejected_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $roleUser = ['employee', 'admin'];
            $admins = User::whereHas('roles', function ($query) use ($roleUser) {
                $query->whereIn('name', $roleUser);
            })->get();

            Notification::send($admins, new PostSubmittedForApprovalNotification($post));

            DB::commit();

            return $this->respondCreated('Tạo bài viết thành công, đang chờ kiểm duyệt', $post);
        } catch (\Exception $e) {
            DB::rollBack();

            if (
                !empty($data['thumbnail']) &&
                filter_var($data['thumbnail'], FILTER_VALIDATE_URL)
            ) {
                $this->deleteImage($data['thumbnail'], self::FOLDER);
            }

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getPostBySlug(string $slug)
    {
        try {
            $user = Auth::user();

            if (!$user || $user !== Auth::user()) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $post = Post::query()
                ->with('category', 'tags')
                ->where('slug', $slug)
                ->first();

            if (!$post) {
                return $this->respondNotFound('Không tìm thấy bài viết');
            }

            return $this->respondOk('Thông tin bài viết: ' . $post->title, $post);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function update(UpdatePostRequest $request, string $slug)
    {
        try {
            DB::beginTransaction();

            $data = $request->except('thumbnail', 'category_id', 'published_at');

            $post = Post::query()
                ->with(['tags'])
                ->where('slug', $slug)
                ->first();

            if ($request->hasFile('thumbnail')) {
                if ($post->thumbnail && filter_var($post->thumbnail, FILTER_VALIDATE_URL)) {
                    $this->deleteImage($post->thumbnail, self::FOLDER);
                }

                $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), self::FOLDER);
            } else {
                $data['thumbnail'] = $post->thumbnail;
            }


            $data['category_id'] = $request->input('category_id') ?? $post->category_id;
            $data['published_at'] = $request->input('published_at') ?? $post->published_at;
            $data['slug'] = !empty($data['title'])
                ? Str::slug($data['title'])
                : $post->slug;
            // Nếu bài viết đã bị từ chối (status = pending), gửi lại để kiểm duyệt
            if ($post->status === 'pending') {
                Approvable::query()->create([
                    'approver_id' => null, // Chưa có người kiểm duyệt
                    'status' => 'pending', // Trạng thái ban đầu là pending
                    'note' => null, // Chưa có ghi chú
                    'reason' => null, // Chưa có lý do chỉnh sửa
                    'content_modification' => 0, // Không yêu cầu chỉnh sửa nội dung
                    'approvable_type' => Post::class, // Loại đối tượng là Post
                    'approvable_id' => $post->id, // ID của bài viết
                    'request_date' => now(), // Ngày yêu cầu kiểm duyệt
                    'approved_at' => null, // Chưa được duyệt
                    'rejected_at' => null, // Chưa bị từ chối
                    'created_at' => now(), // Thời gian tạo bản ghi
                    'updated_at' => now(), // Thời gian cập nhật bản ghi
                ]);
            }
            $post->update($data);
            if (!empty($request->input('tags'))) {
                $tags = collect($request->input('tags'))->map(function ($tagName) {
                    return Tag::firstOrCreate([
                        'name' => $tagName,
                        'slug' => Str::slug($tagName) ?? Str::uuid()
                    ]);
                });

                $post->tags()->sync($tags->pluck('id'));
            } else {
                $post->tags()->detach();
            }

            DB::commit();

            return $this->respondOk('Cập nhật bài viết thành công, đang chờ kiểm duyệt', $post);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
