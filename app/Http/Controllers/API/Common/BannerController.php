<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Banners\StoreBannerRequest;
use App\Http\Requests\API\Banners\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\Course;
use App\Models\Rating;
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

            $totalCourses = Course::query()->where('status', 'approved')->count();

            $systemAverageRating = Rating::query()->whereHas('course', function ($query) {
                $query->where('status', 'approved');
            })->avg('rate');

            return $this->respondOk('Danh sách dữ liệu', [
                'banners' => $banners,
                'system_average_rating' => $systemAverageRating ?? 0,
                'total_courses' => $totalCourses,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

}
