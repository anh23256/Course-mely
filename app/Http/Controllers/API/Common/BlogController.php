<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Posts\StorePostRequest;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
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
            Log::info("Recent posts in session: " . json_encode($recentPosts));  // Log để kiểm tra session
            if (!in_array($slug, $recentPosts)) {
                session()->push('recent_posts', $slug); // Lưu lại vào session
                Log::info('Recent posts after update: ' . json_encode(session()->get('recent_posts')));
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
            Log::info('Recent posts in session (recentViews): ' . json_encode($recentPosts));
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
