<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getPost()
    {
        try {
            $posts = Post::query()
                ->select('id', 'user_id', 'slug', 'category_id', 'title', 'thumbnail', 'created_at', 'views')
                ->with(
                    [
                        'user:id,name,avatar,code',
                        'category:id,name,slug',
                    ]
                )->withCount('comments')
                ->orderByDesc('views')
                ->limit(3)
                ->get()
                ->map(function ($post) {
                    if ($post->thumbnail) {
                        $post->thumbnail = Storage::url($post->thumbnail) ?? '';
                    }
                    return $post;
                });

            return $this->respondOk('Danh sách bài viết', $posts);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
