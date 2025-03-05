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

            return $this->respondOk('Thông tin bài viết: ' . $post->title, $post);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
