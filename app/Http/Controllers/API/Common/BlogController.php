<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Posts\StorePostRequest;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\ViewLog;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index()
    {
        try {
            $posts = Post::query()
                ->with([
                    'user',
                    'category',
                    'tags',
                ])
                ->where('status', Post::STATUS_PUBLISHED)
                ->orderByDesc('views')
                ->paginate(4);

            if (!$posts) {
                return $this->respondNotFound('Không tìm thấy bài viết nào');
            }

            return $this->respondOk('Danh sách bài viết:', $posts);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getBlogBySlug(string $slug)
    {
        try {
            $post = Post::query()
                ->with([
                    'user',
                    'category',
                    'tags',
                ])
                ->where('slug', $slug)
                ->where('status', Post::STATUS_PUBLISHED)
                ->first();

            if (!$post) {
                return $this->respondNotFound('Không tìm thấy bài viết');
            }
            $recentPosts = session()->get('recent_posts', []);

            // Đảm bảo recentPosts luôn là array
            if (!is_array($recentPosts)) {
                $recentPosts = json_decode($recentPosts, true) ?? []; // Nếu là JSON string thì decode, nếu không thì trả về mảng rỗng
            }

            // Log::info("Before update - Recent posts in session: " . json_encode($recentPosts));

            if (!in_array($slug, $recentPosts)) {
                $recentPosts[] = $slug; 
                session()->put('recent_posts', $recentPosts); 
                session()->save(); 
            }
            // Log::info('Recent posts after update: ' . json_encode(session()->get('recent_posts')));

            $ip = request()->ip();
            $viewedPosts = session()->get('viewed_posts', []);
            $hasViewedByIP = ViewLog::where('post_id', $post->id)
                ->where('ip', $ip)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            // Kiểm tra nếu user đã xem bài viết trong session hoặc cùng IP trong 24h
            if (!in_array($post->id, $viewedPosts) && !$hasViewedByIP) {
                $post->increment('views'); // Tăng số lượt xem

                // Lưu vào session để chặn người dùng reload trang liên tục
                $viewedPosts[] = $post->id;
                session()->put('viewed_posts', $viewedPosts);

                // Lưu IP vào database để chặn spam view từ cùng một mạng
                ViewLog::create([
                    'post_id' => $post->id,
                    'ip' => $ip,
                ]);
            }
            return $this->respondOk('Thông tin bài viết: ' . $post->title, $post);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
    public function getBlogsByCategory($slug)
    {
        try {
            $posts = Post::query()
                ->with([
                    'user',
                    'category',
                    'tags',
                ])
                ->where('status', Post::STATUS_PUBLISHED)
                ->whereHas('category', function ($query) use ($slug) {
                    $query->where('slug', $slug);  // Lọc bài viết theo slug của category
                })
                ->paginate(4); // Giới hạn số lượng bài viết trên mỗi trang

            if ($posts->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy bài viết nào trong danh mục này');
            }

            return $this->respondOk('Danh sách bài viết trong danh mục:', $posts);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
    public function getBlogsByTag($slug)
    {
        try {
            $posts = Post::query()
                ->with([
                    'user',
                    'category',
                    'tags',
                ])
                ->where('status', Post::STATUS_PUBLISHED)
                ->whereHas('tags', function ($query) use ($slug) {  // Lọc theo thẻ
                    $query->where('tags.slug', $slug);
                })
                ->paginate(4); // Giới hạn số lượng bài viết trên mỗi trang

            if ($posts->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy bài viết nào với thẻ này');
            }

            return $this->respondOk('Danh sách bài viết với thẻ:', $posts);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
    public function recentViews()
    {
        try {
            $recentPosts = session()->get('recent_posts', []);
            // Log::info('Recent posts in session (recentViews): ' . json_encode($recentPosts));
            if (empty($recentPosts)) {
                return $this->respondNotFound('Không tìm thấy bài viết nào đã xem');
            }

            $posts = Post::with(['user', 'category', 'tags'])
                ->whereIn('slug', $recentPosts)
                ->orderByRaw('FIELD(slug, ' . implode(',', array_map(fn($slug) => "'$slug'", $recentPosts)) . ')')
                ->paginate(4); // Phân trang

            return $this->respondOk('Danh sách bài viết đã xem:', $posts);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
