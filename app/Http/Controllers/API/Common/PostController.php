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
            posts.created_at,
            users.name as author_name,
            users.code as author_code,
            categories.name as category_name,
            categories.slug as category_slug,
            posts.views
        ")
            ->leftJoin('users', 'posts.user_id', '=', 'users.id')
            ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->where('posts.status', 'published')
            ->groupBy('posts.id', 'users.name', 'users.code', 'categories.name', 'categories.slug')
            ->orderByDesc('posts.views')
            ->limit(3)
            ->get();

            return $this->respondOk('Danh sách bài viết', $posts);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
