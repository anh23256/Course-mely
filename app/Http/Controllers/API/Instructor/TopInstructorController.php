<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;

class TopInstructorController extends Controller
{
    //
    use LoggableTrait, ApiResponseTrait;
    public function index()
    {
        try {

            $topInstructor = User::query()->whereHas('roles', function ($query) {
                $query->where('name', 'instructor');
            })
                ->where('status', 1)
                ->whereHas('courses')
                ->withCount('courses')
                ->withSum('courses', 'total_student')
                ->orderByDesc('courses_count')
                ->orderByDesc('courses_sum_total_student')
                ->limit(4)
                ->get();

            return $this->respondOk('Top 4 giảng viên :', $topInstructor);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
