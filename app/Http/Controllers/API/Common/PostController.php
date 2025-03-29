<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;


class PostController extends Controller
{
    //
    use LoggableTrait, ApiResponseTrait;
    public function getPost()
    {
        try {
            $posts = Post::selectRaw("
        posts.id,
        posts.title,
        posts.thumbnail,
        posts.views,
        users.name as author_name,
        users.code as author_code,
        categories.name as category_name,
        categories.slug as category_slug
    ")
                ->leftJoin('users', 'posts.user_id', '=', 'users.id')
                ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
                ->where('posts.status', 'published')
                ->groupBy('posts.id', 'users.name', 'users.code', 'categories.name', 'categories.slug')
                ->orderByDesc('posts.views')
                ->limit(3)
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'thumbnail' => $post->thumbnail,
                        'views' => $post->views,
                        'author' => [
                            'name' => $post->author_name,
                            'code' => $post->author_code
                        ],
                        'category' => [
                            'name' => $post->category_name,
                            'slug' => $post->category_slug
                        ]
                    ];
                });

            
            return $this->respondOk('Danh sách bài viết', $posts);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
