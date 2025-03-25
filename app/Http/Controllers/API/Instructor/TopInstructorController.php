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

            $topInstructor = User::selectRaw("
                    users.id, 
                    users.name, 
                    users.code, 
                    users.avatar, 
                    COUNT(DISTINCT courses.id) as total_courses, 
                    ROUND(AVG(ratings.rate), 1) as avg_rating,
                    COUNT(DISTINCT follows.id) as total_followers
                ")      
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', 'instructor')
                    ->where('users.status', 'active')
                    ->leftJoin('courses', 'users.id', '=', 'courses.user_id') 
                    ->leftJoin('ratings', 'courses.id', '=', 'ratings.course_id')
                    ->leftJoin('follows', 'users.id', '=', 'follows.instructor_id')
                    ->groupBy('users.id')
                    ->orderByDesc('avg_rating') 
                    ->orderByDesc('total_followers') 
                    ->limit(5)
                    ->get();


            return $this->respondOk('Top 4 giảng viên :', $topInstructor);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
