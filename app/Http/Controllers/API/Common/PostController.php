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
            COUNT(comments.id) as total_comments,
            JSON_OBJECT('name', users.name, 'code', users.code) as author,
            JSON_OBJECT('name', categories.name, 'slug', categories.slug) as category,
            posts.views
        ")
            ->leftJoin('users', 'posts.user_id', '=', 'users.id')
            ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->leftJoin('comments', function ($join) {
                $join->on('posts.id', '=', 'comments.commentable_id')
                    ->whereRaw("comments.commentable_type = ?", [Post::class]);
            })
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
