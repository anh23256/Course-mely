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
            $filteredBlogs = $posts->map(function ($post) {
                $commentCount = $post->comments()->count();

                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'description' => $post->description,
                    'thumbnail' => $post->thumbnail,
                    'views' => $post->views,
                    'comment_count' => $commentCount,
                    'published_at' => $post->published_at,
                    'user' => $post->user ? [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatar,
                        'email' => $post->user->email,
                    ] : null, 
                    'category' => $post->category ? [
                        'id' => $post->category->id,
                        'name' => $post->category->name,
                        'slug' => $post->category->slug,
                    ] : null,

                    'tags' => $post->tags ? $post->tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'slug' => $tag->slug,
                        ];
                    }) : [],
                ];
            });
            return response()->json([
                'message' => 'Danh sách bài viết:',
                'data' => $filteredBlogs,
                'pagination' => [
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                ]
            ], 200);
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
                    'comments',
                    'profile',
                ])
                ->where('slug', $slug)
                ->where('status', Post::STATUS_PUBLISHED)
                ->first();
            $commentCount = $post->comments()->count();

            // Thêm số bình luận vào phản hồi
            $post->comment_count = $commentCount;
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
            $post['profile']= $post->user->profile->about_me ?? null;
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
                'comments',
            ])
            ->where('status', Post::STATUS_PUBLISHED)
            ->whereHas('category', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->paginate(4);

        if ($posts->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy bài viết nào trong danh mục này'
            ], 404);
        }

        $filteredBlogs = collect($posts->items())->map(function ($post) {
            $commentCount = $post->comments ? $post->comments->count() : 0;
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'description' => $post->description,
                'thumbnail' => $post->thumbnail,
                'views' => $post->views,
                'comment_count' => $commentCount,
                'published_at' => $post->published_at,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'avatar' => $post->user->avatar,
                    'email' => $post->user->email,
                ],
                'category' => [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                ]
            ];
        });

        return response()->json([
            'message' => 'Danh sách bài viết trong danh mục:',
            'data' => $filteredBlogs,
            'pagination' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ]
        ], 200);
    } catch (\Exception $e) {
        Log::error($e);
        return response()->json([
            'message' => 'Có lỗi xảy ra, vui lòng thử lại'
        ], 500);
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
                    'comments',
                ])
                ->where('status', Post::STATUS_PUBLISHED)
                ->whereHas('tags', function ($query) use ($slug) {  // Lọc theo thẻ
                    $query->where('tags.slug', $slug);
                })
                ->paginate(4); // Giới hạn số lượng bài viết trên mỗi trang

            if ($posts->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy bài viết nào với thẻ này');
            }
            $filteredBlogs = collect($posts->items())->map(function ($post) {
                $commentCount = $post->comments ? $post->comments->count() : 0;
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'description' => $post->description,
                    'thumbnail' => $post->thumbnail,
                    'views' => $post->views,
                    'comment_count' => $commentCount,
                    'published_at' => $post->published_at,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatar,
                        'email' => $post->user->email,
                    ],
                    'category' => [
                        'id' => $post->category->id,
                        'name' => $post->category->name,
                        'slug' => $post->category->slug,
                    ]
                ];
            });
            return response()->json([
                'message' => 'Danh sách bài viết với thẻ:',
                'data' => $filteredBlogs,
                'pagination' => [
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                ]
            ], 200);
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
