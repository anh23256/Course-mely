<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PostsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Posts\StorePostRequest;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Notifications\CrudNotification;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\isEmpty;

class PostController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, FilterTrait;

    const FOLDER = 'blogs';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $title = 'Quản lý bài viết';
            $subTitle = 'Danh sách bài viết';

            $categories = Category::query()->get();
            $queryPost = Post::with(['user:id,name', 'category:id,name']);

            if ($request->hasAny(['title', 'user_name_post', 'category_id', 'status', 'startDate', 'endDate']))
                $queryPost = $this->filter($request, $queryPost);

            if ($request->has('search_full'))
                $queryPost = $this->search($request->search_full, $queryPost);

            $posts = $queryPost->paginate(10);

            if ($request->ajax()) {
                $html = view('posts.table', compact(['posts']))->render();
                return response()->json(['html' => $html]);
            }

            return view('posts.index', compact([
                'title',
                'subTitle',
                'categories',
                'posts'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $title = 'Quản lý bài viết';
            $subTitle = 'Thêm mới bài viết';

            $categories = Category::query()->get();
            $tags = Tag::query()->get();

            return view('posts.create', compact([
                'title',
                'subTitle',
                'categories',
                'tags'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        try {

            DB::beginTransaction();

            $data = $request->except('thumbnail');

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), self::FOLDER);
            }

            $data['user_id'] = Auth::id();

            $data['category_id'] = $request->input('categories');

            $data['published_at'] = $request->input('published_at') ?? now();

            do {
                $data['slug'] = Str::slug($request->title) . '-' . substr(Str::uuid(), 0, 10);
            } while (Post::query()->where('slug', $data['slug'])->exists());

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

            DB::commit();

            return redirect()->route('admin.posts.index')->with('success', 'Thao tác thành công');
        } catch (\Exception $e) {

            DB::rollBack();

            if (
                !empty($data['thumbnail'])
                && filter_var($data['thumbnail'], FILTER_VALIDATE_URL)
            ) {
                $this->deleteImage($data['thumbnail'], 'posts');
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $title = 'Quản lý bài viết';
            $subTitle = 'Chi tiết bài viết';

            $post = Post::query()
                ->with(['tags:id,name', 'category:id,name,parent_id', 'user:id,name'])
                ->findOrFail($id);

            return view('posts.show', compact([
                'title',
                'subTitle',
                'post',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Không tìm thấy bài viết');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $title = 'Quản lý bài viết';
            $subTitle = 'Cập nhật bài viết';

            $categories = Category::query()->get();
            $tags = Tag::query()->get();
            $post = Post::query()
                ->with(['tags:id,name', 'category:id,name,parent_id'])
                ->findOrFail($id);

            $categoryIds = $post->category->pluck('id')->toArray();
            $tagIds = $post->tags->pluck('id')->toArray();

            return view('posts.edit', compact([
                'title',
                'subTitle',
                'categories',
                'tags',
                'post',
                'categoryIds',
                'tagIds'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Không tìm thấy bài viết');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $data = $request->except('thumbnail', 'categories', 'is_hot');

            $post = Post::query()->with(['tags'])->findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                if ($post->thumbnail && filter_var($post->thumbnail, FILTER_VALIDATE_URL)) {
                    $this->deleteImage($post->thumbnail, self::FOLDER);
                }

                $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), self::FOLDER);
            }

            $data['is_hot'] = $request->input('is_hot') ?? 0;
            $data['category_id'] = $request->input('categories');
            $data['published_at'] = $request->input('published_at') ?? now();

            do {
                $data['slug'] = Str::slug($request->title) . '-' . substr(Str::uuid(), 0, 10);
            } while (Post::query()->where('slug', $data['slug'])->exists());

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

            CrudNotification::sendToMany([], $id);

            return redirect()->route('admin.posts.edit', $id)->with('success', 'Cập nhật bài viết thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['thumbnail']) && !empty($data['thumbnail']) && filter_var($data['thumbnail'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['thumbnail'], self::FOLDER);
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Cập nhật bài viết không thành công');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();

            if (isset($category->icon)) {
                $this->deleteImage($post->thubmnail, self::FOLDER);
            }
            return response()->json($data = ['status' => 'success', 'message' => 'Mục đã được xóa.']);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json($data = ['status' => 'error', 'message' => 'Lỗi thao tác.']);
        }
    }
    public function export()
    {
        try {
            
            return Excel::download(new PostsExport, 'Posts.xlsx');

        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function filter(Request $request, $query)
    {
        $filters = [
            'title' => ['queryWhere' => 'LIKE'],
            'category_id' => ['queryWhere' => '='],
            'status' => ['queryWhere' => '='],
            'user_name_post' => null,
            'deleted_at' => ['attribute' => ['start_deleted' => '>=', 'end_deleted' => '<=',]],
            'published_at' => ['attribute' => ['startDate' => '>=', 'endDate' => '<=',]]
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }

    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%$searchTerm%")
                    ->orWhereHas('user', function ($q) use ($searchTerm) {
                        $q->where('name', 'LIKE', "%$searchTerm%");
                    });
            });
        }

        return $query;
    }
    public function forceDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $postID = explode(',', $id);

                $this->deleteposts($postID);
            } else {
                $post = Post::query()->onlyTrashed()->findOrFail($id);

                $post->forceDelete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }
    private function deletePosts(array $postID)
    {

        $posts = Post::query()->whereIn('id', $postID)->withTrashed()->get();

        foreach ($posts as $post) {

            $thumbnail = $post->thumbnail;

            if ($post->trashed()) {
                $post->forceDelete();
            } else {
                $post->delete();

                if (
                    isset($thumbnail) && !empty($thumbnail)
                    && filter_var($thumbnail, FILTER_VALIDATE_URL)
                ) {
                    $this->deleteImage($thumbnail, self::FOLDER);
                }
            }
        }
    }
    public function restoreDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $postID = explode(',', $id);

                $this->restoreDeletePosts($postID);
            } else {
                $post = Post::query()->onlyTrashed()->findOrFail($id);

                if ($post->trashed()) {
                    $post->restore();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Khôi phục thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Khôi phục thất bại'
            ]);
        }
    }
    public function listPostDelete(Request $request)
    {
        try {
            $title = 'Quản lý bài viết';
            $subTitle = 'Danh sách bài viết đã xóa';
            $post_deleted_at = true;

            $categories = Category::query()->get();

            $queryPost = Post::with(['user:id,name', 'category:id,name'])->onlyTrashed();

            if ($request->hasAny(['title', 'user_name_post', 'category_id', 'status', 'start_deleted', 'end_deleted']))
                $queryPost = $this->filter($request, $queryPost);

            if ($request->has('search_full'))
                $queryPost = $this->search($request->search_full, $queryPost);

            $posts = $queryPost->paginate(10);

            if ($request->ajax()) {
                $html = view('posts.table', compact(['posts', 'post_deleted_at']))->render();
                return response()->json(['html' => $html]);
            }
            return view('posts.list-post-delete', compact([
                'title',
                'subTitle',
                'categories',
                'posts'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }
    private function restoreDeletePosts(array $postID)
    {

        $posts = Post::query()->whereIn('id', $postID)->onlyTrashed()->get();

        foreach ($posts as $post) {

            $thumbnail = $post->thumbnail;

            if ($post->trashed()) {
                $post->restore();
            }
        }
    }
}
