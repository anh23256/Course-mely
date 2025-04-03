<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DashBoardExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TopInstructorController extends Controller
{
    use LoggableTrait;
    const RATE = 0.4;
    public function index(Request $request)
    {
        try {
            $title = 'Dashboard';
            $year = now()->year;

            $queryTopInstructors = $this->getTopInstructor($request);
            $quertTopInstructorsFollows = $this->getTopInstructorFollow();
            $queryTopInstructorsStudents = $this->getTopInstructorStudent($request);

            list(
                $quertTopInstructorsFollows,
            ) = $this->getFilterDataChart(
                $request,
                $quertTopInstructorsFollows,
            );

            $topInstructors = DB::table(DB::raw("({$queryTopInstructors->toSql()}) as sub"))
                ->mergeBindings($queryTopInstructors)
                ->paginate(5);

            $topInstructorsFollows = $quertTopInstructorsFollows->get();
            $topInstructorsStudents = $queryTopInstructorsStudents->get();

            if ($request->ajax()) {
                return response()->json([
                    'top_instructors_table' => view('revenue-statistics.includes.top_instructors', compact('topInstructors'))->render(),
                    'pagination_links_instructors' => $topInstructors->links()->toHtml(),
                    'topInstructorsFollows' => $topInstructorsFollows,
                    'topInstructor' => $topInstructors,
                    'topInstructorsStudents' => $topInstructorsStudents
                ]);
            }

            return view('top-instructors.index', compact([
                'title',
                'topInstructors',
                'topInstructorsFollows',
                'topInstructorsStudents'
            ]));
        } catch (\Exception $e) {
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

    private function getFilterDataChart(
        Request $request,
        $quertTopInstructorsFollows,
    ) {
        $quertTopInstructorsFollows = $this->applyGlobalFilter($quertTopInstructorsFollows, $request, 'users', 'created_at');
        return [
            $quertTopInstructorsFollows,
        ];
    }
    private function getTopInstructorFollow()
    {
        $users = DB::table('users')
            ->select('users.id', 'users.name', 'users.avatar', 'users.code', 'users.created_at')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', function ($join) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'instructor');
            })
            ->where('users.status', '!=', 'blocked');

        return DB::table(DB::raw("({$users->toSql()}) as users"))
            ->mergeBindings($users)
            ->leftJoinSub(
                DB::table('follows')
                    ->select('instructor_id', DB::raw('COUNT(id) as total_follow'))
                    ->groupBy('instructor_id'),
                'follows',
                'follows.instructor_id',
                '=',
                'users.id'
            )
            ->select(
                'users.id',
                'users.name',
                'users.code',
                'users.avatar',
                DB::raw('COALESCE(follows.total_follow, 0) as total_follow'),
            )
            ->orderByDesc('total_follow')
            ->limit(10);
    }
    private function getTopInstructorStudent(Request $request)
    {
        $users = DB::table('users')
            ->select('users.id', 'users.name', 'users.avatar', 'users.code', 'users.created_at')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', function ($join) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'instructor');
            })
            ->where('users.status', '!=', 'blocked');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $year = now()->year;

        if (!empty($startDate) && !empty($endDate)) {
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
        } else {
            $startDate = "{$year}-01-01 00:00:00";
            $endDate = now()->endOfDay()->format('Y-m-d H:i:s');
        }

        $subQuery = DB::raw("
                (SELECT courses.user_id, COUNT(DISTINCT course_users.user_id) as total_enrolled_students
                 FROM courses
                 LEFT JOIN course_users ON courses.id = course_users.course_id
                 WHERE course_users.access_status = 'active'
                   AND course_users.created_at BETWEEN '{$startDate}' AND '{$endDate}'
                 GROUP BY courses.user_id) as courses
            ");

        return DB::table(DB::raw("({$users->toSql()}) as users"))
            ->mergeBindings($users)
            ->leftJoin($subQuery, 'courses.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.code',
                'users.avatar',
                DB::raw('COALESCE(courses.total_enrolled_students, 0) as total_student')
            )
            ->orderByDesc('total_student')
            ->limit(10);
    }
    private function getTopInstructor(Request $request)
    {
        $invoices = DB::table('invoices')->where('invoices.status', 'Đã thanh toán');
        $invoices = $this->applyGlobalFilter($invoices, $request, 'invoices', 'created_at');

        $query = DB::table('courses')
            ->select(
                'courses.user_id',
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                'users.created_at',
                DB::raw('ROUND(SUM(invoices.final_amount) * 0.6, 0) as total_revenue'),
            )
            ->leftJoinSub($invoices, 'invoices', function ($join) {
                $join->on('invoices.course_id', '=', 'courses.id');
            })
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'courses.user_id')
                    ->where('users.status', '!=', 'blocked');
            });

        $totalRevenue = $query
            ->groupBy('courses.user_id', 'users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('total_revenue')
            ->limit(10);

        $id_topCourse = (clone $totalRevenue)->pluck('user_id')->toArray();

        $totalCourses = DB::table('courses')
            ->select('user_id', DB::raw('COUNT(id) as total_courses'))
            ->whereIn('user_id', $id_topCourse)
            ->groupBy('user_id');

        $totalStudents = DB::table(table: 'courses')
            ->select('courses.user_id', DB::raw('COALESCE(COUNT(DISTINCT course_users.user_id), 0) as total_enrolled_students'))
            ->leftJoin('course_users', function ($join) {
                $join->on('courses.id', '=', 'course_users.course_id')
                    ->where(['course_users.source' => 'purchase', 'course_users.access_status' => 'active']);
            })
            ->whereIn('courses.user_id', $id_topCourse)
            ->groupBy('courses.user_id');

        return DB::table(DB::raw("({$totalRevenue->toSql()}) as invoices"))
            ->mergeBindings($totalRevenue)
            ->leftJoinSub($totalCourses, 'courses', 'courses.user_id', '=', 'invoices.user_id')
            ->leftJoinSub($totalStudents, 'students', 'students.user_id', '=', 'invoices.user_id')
            ->select(
                'invoices.id',
                'invoices.name',
                'invoices.email',
                'invoices.avatar',
                'invoices.created_at',
                DB::raw('COALESCE(invoices.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(courses.total_courses, 0) as total_courses'),
                DB::raw('COALESCE(students.total_enrolled_students, 0) as total_enrolled_students')
            )
            ->orderByDesc('total_revenue');
    }
}
