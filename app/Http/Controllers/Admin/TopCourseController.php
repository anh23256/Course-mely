<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DashBoardExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\User;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TopCourseController extends Controller
{
    use LoggableTrait;
    const RATE = 0.4;
    public function index(Request $request)
    {
        try {
            $title = 'Dashboard';
            $year = now()->year;

            $queryTopCourses = $this->getTopCourse($request);
            $queryCourseRatings = $this->getCourseRating();
            $queryTopCoursesProgress = $this->getTopCourseProgress();
            $queryGetTopViewCourses = $this->getTopViewCourse();
            $queryGetTopViewCourses = $this->filterTopCourseView($queryGetTopViewCourses, $request);

            list(
                $queryCourseRatings,
                $queryTopCoursesProgress,
                $queryGetTopViewCourses,
            ) = $this->getFilterDataChart(
                $request,
                $queryCourseRatings,
                $queryTopCoursesProgress,
                $queryGetTopViewCourses,
            );

            $topCourses = $queryTopCourses->paginate(10);

            $courseRatings = $queryCourseRatings->get();
            $topCoursesProgress = $queryTopCoursesProgress->get();
            $getTopViewCourses = $queryGetTopViewCourses->get();

            if ($request->ajax()) {
                return response()->json([
                    'top_courses_table' => view('revenue-statistics.includes.top_courses', compact('topCourses'))->render(),
                    'getTopViewCourses' => view('revenue-statistics.includes.top_views', compact('getTopViewCourses'))->render(),
                    'course_rating' => $courseRatings,
                    'topCourses' => $topCourses,
                    'topCoursesProgress' => $topCoursesProgress
                ]);
            }

            return view('top-courses.index', compact([
                'title',
                'topCourses',
                'courseRatings',
                'topCoursesProgress',
                'getTopViewCourses',
            ]));
        } catch (\Throwable $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Lấy dữ liệu không thành công vui lòng thử lại');
        }
    }

    private function applyGlobalFilter($query, Request $request, $table, $column)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $year = now()->year;

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween("{$table}.{$column}", [$startDate, $endDate]);
        } else {
            $query->where("{$table}.{$column}", '>=', "{$year}-01-01 00:00:00")
                ->where("{$table}.{$column}", '<=', now()->endOfDay());
        }

        return $query;
    }

    private function filterTopCourseView($query, Request $request)
    {
        $orderby_course = $request->input('orderby_course', 'views');

        switch ($orderby_course) {
            case 'created_at':
                $query->orderBy('courses.created_at', 'desc');
                break;

            case 'price_asc':
                $query->orderBy('courses.price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('courses.price', 'desc');
                break;
            default:
                $query->orderBy('courses.views', 'desc');
                break;
        }

        return $query;
    }

    private function getFilterDataChart(
        Request $request,
        $queryCourseRatings,
        $queryTopCoursesProgress,
        $queryGetTopViewCourses,
    ) {
        $queryCourseRatings = $this->applyGlobalFilter($queryCourseRatings, $request, 'courses', 'created_at');
        $queryTopCoursesProgress = $this->applyGlobalFilter($queryTopCoursesProgress, $request, 'course_users', 'created_at');
        $queryGetTopViewCourses = $this->applyGlobalFilter($queryGetTopViewCourses, $request, 'courses', 'created_at');
        return [
            $queryCourseRatings,
            $queryTopCoursesProgress,
            $queryGetTopViewCourses,
        ];
    }
    private function getTopViewCourse()
    {
        return DB::table('courses')
            ->select('courses.name', 'users.name as instructor_name', 'users.avatar as instructor_avatar', 'courses.thumbnail', 'courses.views', 'courses.price', 'courses.price_sale', 'courses.is_free', 'courses.slug', 'courses.id')
            ->leftJoinSub(
                DB::table('users')
                    ->select('id', 'name', 'avatar'),
                'users',
                function ($join) {
                    $join->on('users.id', '=', 'courses.user_id');
                }
            )
            ->limit(10);
    }

    private function getTopCourseProgress()
    {
        return CourseUser::selectRaw('course_id, ROUND(AVG(progress_percent),2) as avg_progress')
            ->groupBy('course_id')
            ->orderByDesc('avg_progress')
            ->with('course:id,name')
            ->limit(10);
    }

    private function getTopCourse(Request $request)
    {
        $totalRevenue = DB::table('invoices')
            ->select('course_id', DB::raw('SUM(final_amount) as total_revenue'), DB::raw('COUNT(id) as total_sales'))
            ->where('status', 'Đã thanh toán')
            ->orderByDesc('total_revenue')->groupBy('course_id')->limit(10);

        $totalRevenue = $this->applyGlobalFilter($totalRevenue, $request, 'invoices', 'created_at');

        $totalStudents = DB::table('course_users')
            ->select('course_id', DB::raw('COUNT(user_id) as total_enrolled_students'))
            ->where(['source' => 'purchase', 'access_status' => 'active'])
            ->groupBy('course_id');

        return DB::table('courses')
            ->leftJoinSub($totalRevenue, 'invoices', 'invoices.course_id', '=', 'courses.id')
            ->leftJoinSub($totalStudents, 'students', 'students.course_id', '=', 'courses.id')
            ->select(
                'courses.id',
                'courses.name',
                'courses.thumbnail',
                'courses.created_at',
                DB::raw('COALESCE(invoices.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(students.total_enrolled_students, 0) as total_enrolled_students'),
                DB::raw('COALESCE(invoices.total_sales, 0) as total_sales')
            )
            ->orderByDesc('total_revenue')
            ->take(10);
    }
    private function getCourseRating()
    {
        return DB::table(DB::raw('(SELECT course_id, FLOOR(AVG(rate)) as rating FROM ratings GROUP BY course_id) as subquery'))
            ->join('courses', 'subquery.course_id', '=', 'courses.id')
            ->selectRaw('rating, COUNT(course_id) as total_courses')
            ->groupBy('rating')
            ->orderBy('rating', 'desc');
    }
}
