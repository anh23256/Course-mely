<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;


class PostController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    
    public function getPost()
    {
        try {

            $posts = Post::query()
            ->select('id','user_id', 'category_id' ,'title', 'thumbnail', 'created_at', 'views')
            ->with(
                [
                    'user:id,name,avatar,code',
                    'category:id,name,slug',
                ]
            )->withCount('comments')
            ->orderByDesc('views')
            ->limit(3)
            ->get();


            
            return $this->respondOk('Danh sách bài viết', $posts);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
