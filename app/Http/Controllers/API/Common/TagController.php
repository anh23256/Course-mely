<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index()
    {
        try {
            $tags = Tag::query()->get();

            if ($tags->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách thẻ', $tags);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
