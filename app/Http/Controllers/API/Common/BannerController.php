<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Banners\StoreBannerRequest;
use App\Http\Requests\API\Banners\UpdateBannerRequest;
use App\Models\Banner;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;

class BannerController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, ApiResponseTrait;

    public function index()
    {
        try {
            $banners = Banner::query()
                ->where('status', 1)
                ->orderBy('order')
                ->get();

            if ($banners->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách dữ liệu', $banners);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

}
