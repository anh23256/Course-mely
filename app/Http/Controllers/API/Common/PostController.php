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
            $post = Post::selectRaw("
            posts.id,
            posts.title,
            posts.thumbnail,
            posts.created_at,
            users.name as author,
            categories.name as category,
            posts.views,
            COUNT(DISTINCT comments.id) as total_comments
        ")
                ->leftJoin('users', 'posts.user_id', '=', 'users.id')
                ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
                ->leftJoin('comments', function ($join) {
                    $join->on('posts.id', '=', 'comments.commentable_id')
                        ->whereRaw("comments.commentable_type = ?", [Post::class])
                        ->whereNull('comments.parent_id');
                })
                ->where('posts.status', 'published')
                ->groupBy('posts.id', 'users.name', 'categories.name')
                ->orderByDesc('posts.created_at')
                ->limit(5)
                ->get();

            return $this->respondOk('Danh sách bài viết', $post);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
