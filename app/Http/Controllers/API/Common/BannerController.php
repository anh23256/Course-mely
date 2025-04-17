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
            
            $systemAverageRating = round($systemAverageRating ?? 0, 1);

            $popularCourses = Course::query()
                ->where('status', 'approved')
                ->where('is_popular', '=', 1)
                ->where('visibility', '=', 'public')
                ->orderBy('total_student', 'desc')
                ->limit(4)
                ->get();
                $slides = $popularCourses->map(function ($course, $index) {
                    return [
                        'id' => $course->id,
                        'title' => $course->name, // Tiêu đề của khóa học
                        'content' => $course->description ?? 'Khóa học nổi bật', // Nội dung mô tả khóa học
                        'image' => $course->thumbnail, // Ảnh của khóa học
                        'price' => $course->price,
                        'is_free'=>$course->is_free,
                        'total_student'=>$course->total_student,
                        'order' => $index + 1, // Đặt thứ tự hiển thị
                        'type' => 'course', // Đánh dấu là khóa học
                    ];
                });
        
                // Nếu khóa học chưa đủ 4, bổ sung banner vào danh sách
                while ($slides->count() < 4 && $banners->isNotEmpty()) {
                    $banner = $banners->shift();
                    $slides->push([
                        'id' => $banner->id,
                        'title' => $banner->title ?? 'Banner quảng cáo',
                        'content' => $banner->content ?? 'Thông tin quảng cáo',
                        'image' => $banner->image,
                        'order' => $banner->order,
                        'type' => 'banner', // Đánh dấu là banner
                    ]);
                }
        
            return $this->respondOk('Danh sách dữ liệu', [
                'slides' => $slides,
                'system_average_rating' => $systemAverageRating ?? 0,
                'total_courses' => $totalCourses,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
