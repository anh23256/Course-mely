<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseUser;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopCourseController extends Controller
{
    use LoggableTrait;
    public function index()
    {
        try {
            $getBestSellingCourses = DB::table('courses')
                ->leftJoin('invoices', 'courses.id', '=', 'invoices.course_id')
                ->select('courses.slug', 'courses.name', DB::raw('COUNT(invoices.id) as total_sales, SUM(invoices.final_amount) as total_amount'))
                ->where('invoices.status', 'Đã thanh toán')
                ->groupBy('courses.slug', 'courses.name')
                ->orderByDesc('total_sales')
                ->limit(10)
                ->get();

            $topCoursesProgress = CourseUser::selectRaw('course_id, ROUND(AVG(progress_percent),2) as avg_progress')
                ->groupBy('course_id')
                ->orderByDesc('avg_progress')
                ->with('course:id,name')
                ->limit(10)
                ->get();

            $topInstructors = DB::table('users')
                ->leftJoin('courses', 'users.id', '=', 'courses.user_id')
                ->leftJoin('invoices', 'courses.id', '=', 'invoices.course_id')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'instructor')
                ->where('users.status', '!=', 'blocked')
                ->select(
                    'users.name',
                    'users.code',
                    'users.avatar',
                    DB::raw('SUM(invoices.final_amount)*0.6 as total_revenue'),
                )
                ->groupBy('users.name', 'users.code', 'users.avatar')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)->get();

            $getTopViewCourses = DB::table('courses')
                ->select('courses.name', 'users.name as instructor_name', 'courses.thumbnail', 'courses.views', 'courses.price', 'courses.price_sale')
                ->join('users', 'users.id', '=', 'courses.user_id')
                ->orderByDesc('courses.views')
                ->limit(10)
                ->get();

            $topInstructorsFollows = DB::table('users')
                ->leftJoin('courses', 'users.id', '=', 'courses.user_id')
                ->leftJoin('follows', 'follows.instructor_id', '=', 'users.id')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'instructor')
                ->where('users.status', '!=', 'blocked')
                ->select(
                    'users.name',
                    'users.code',
                    'users.avatar',
                    DB::raw('COUNT(follows.id) as total_follow, SUM(courses.total_student) as total_student'),
                )
                ->groupBy('users.name', 'users.code', 'users.avatar')
                ->orderBy('total_follow', 'desc')
                ->limit(10)->get();

            return view('top-courses.index', compact([
                'getBestSellingCourses',
                'topCoursesProgress',
                'topInstructors',
                'getTopViewCourses',
                'topInstructorsFollows'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Lấy dữ liệu không thành công, vui lòng thử lại');
        }
    }
}
