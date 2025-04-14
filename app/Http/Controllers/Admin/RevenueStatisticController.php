<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DashBoardExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RevenueStatisticController extends Controller
{
    use LoggableTrait;
    const RATE = 0.4;
    public function index(Request $request)
    {
        try {
            $title = 'Thống kê doanh thu';
            $year = now()->year;

            $queryTopInstructors = $this->getTopInstructor($request);
            $queryTopUsers = $this->getTopUser($request);
            $queryTopCourses = $this->getTopCourse($request);
            $querySystem_Funds = $this->getSystemFund();

            $querySystem_Funds = $this->getFilterDataChart(
                $request,
                $querySystem_Funds,
            );

            $topInstructors = DB::table(DB::raw("({$queryTopInstructors->toSql()}) as sub"))
                ->mergeBindings($queryTopInstructors)
                ->paginate(5);

            $topUsers = DB::table(DB::raw("({$queryTopUsers->toSql()}) as sub"))
                ->mergeBindings($queryTopUsers)
                ->paginate(5);

            $topCourses = DB::table(DB::raw("({$queryTopCourses->toSql()}) as sub"))
                ->mergeBindings($queryTopCourses)
                ->paginate(5);

            $system_Funds = $querySystem_Funds->get();

            if ($request->ajax()) {
                return response()->json([
                    'top_courses_table' => view('revenue-statistics.includes.top_courses', compact('topCourses'))->render(),
                    'top_instructors_table' => view('revenue-statistics.includes.top_instructors', compact('topInstructors'))->render(),
                    'top_users_table' => view('revenue-statistics.includes.top_users', compact('topUsers'))->render(),
                    'pagination_links_courses' => $topCourses->links()->toHtml(),
                    'pagination_links_instructors' => $topInstructors->links()->toHtml(),
                    'pagination_links_users' => $topUsers->links()->toHtml(),
                    'system_Funds' => $system_Funds,
                    'topCourses' => $topCourses,
                    'topInstructors' => $topInstructors,
                    'topUsers' => $topUsers,
                ]);
            }

            return view('revenue-statistics.index', compact([
                'title',
                'topInstructors',
                'topCourses',
                'topUsers',
                'system_Funds',
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
        $querySystem_Funds,
    ) {
        $querySystem_Funds = $this->applyGlobalFilter($querySystem_Funds, $request, 'invoices', 'created_at');
        return $querySystem_Funds;
    }

    public function export(Request $request)
    {
        try {
            $type = $request->input('type');
            $data = $request->input('data');
            $formattedData = [];

            if (!$data) {
                return;
            }

            switch ($type) {
                case 'top_instructor':
                    $headings = ['Người hướng dẫn', 'Khóa học đã bán', 'Học viên', 'Doanh thu'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['name'], $item['total_courses'], $item['total_enrolled_students'], $item['total_revenue'])) {
                            return;
                        }
                        return [
                            $item['name'],
                            number_format($item['total_courses'] ?? 0),
                            number_format($item['total_enrolled_students'] ?? 0),
                            number_format($item['total_revenue'] ?? 0)
                        ];
                    }, $data);
                    break;

                case 'top_course':
                    $headings = ['Khóa học', 'Đã bán', 'Người học', 'Doanh thu'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['name'], $item['total_sales'], $item['total_enrolled_students'], $item['total_revenue'])) {
                            return;
                        }
                        return [
                            $item['name'],
                            number_format($item['total_sales'] ?? 0),
                            number_format($item['total_enrolled_students'] ?? 0),
                            number_format($item['total_revenue'] ?? 0)
                        ];
                    }, $data);
                    break;

                case 'top_student':
                    $headings = ['Học viên', 'Khóa học đã mua', 'Tổng tiền đã chi', 'Lần mua gần nhất'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['name'], $item['total_courses_purchased'], $item['total_spent'], $item['last_purchase_date'])) {
                            return;
                        }
                        return [
                            $item['name'],
                            number_format($item['total_courses_purchased'] ?? 0),
                            number_format($item['total_spent'] ?? 0),
                            $item['last_purchase_date'] ?? ''
                        ];
                    }, $data);
                    break;

                default:
                    return;
            }

            if (empty($formattedData)) {
                $formattedData[] = ['Không có dữ liệu', '', '', ''];
            }

            return Excel::download(new DashBoardExport($formattedData, $headings), $type . '_export.xlsx');
        } catch (\Exception $e) {
            $this->logError($e);

            return;
        }
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
                DB::raw('ROUND(SUM(invoices.final_amount*invoices.instructor_commissions), 0) as total_revenue'),
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

    private function getTopCourse(Request $request)
    {
        $totalRevenue = DB::table('invoices')
            ->select('course_id', DB::raw('SUM(final_amount*instructor_commissions) as total_revenue'), DB::raw('COUNT(id) as total_sales'))
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

    private function getTopUser(Request $request)
    {
        $invoices = DB::table('invoices')
            ->select(
                'user_id',
                DB::raw('COUNT(DISTINCT course_id) as total_courses_purchased'),
                DB::raw('SUM(final_amount) as total_spent'),
                DB::raw('MAX(created_at) as last_purchase_date'),
            )
            ->where('status', 'Đã thanh toán')
            ->orderByDesc('total_spent')
            ->groupBy('user_id');

        $invoices = $this->applyGlobalFilter($invoices, $request, 'invoices', 'created_at');

        $course_user = DB::table('course_users')
            ->select('user_id', DB::raw('COUNT(DISTINCT course_id) as total_active_courses'))
            ->where(['source' => 'purchase', 'access_status' => 'active'])
            ->groupBy('user_id');

        return DB::table('users')
            ->leftJoinSub($invoices,  'invoices', 'invoices.user_id', '=', 'users.id')
            ->leftJoinSub($course_user,  'course_users', 'course_users.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                DB::raw('COALESCE(invoices.total_courses_purchased, 0) as total_courses_purchased'),
                DB::raw('COALESCE(invoices.total_spent, 0) as total_spent'),
                DB::raw('COALESCE(invoices.last_purchase_date, NULL) as last_purchase_date')
            )
            ->orderByDesc('total_spent')
            ->take(10);
    }

    private function getSystemFund()
    {
        return DB::table('invoices')
            ->selectRaw('
            MONTH(created_at) as month,
            YEAR(created_at) as year,
            ROUND(SUM(final_amount), 0) as total_revenue,
            ROUND(SUM(final_amount *(1 - instructor_commissions)), 0) as total_profit,
            SUM(CASE WHEN invoice_type = "course" THEN 1 ELSE 0 END) as total_course_sales,
            SUM(CASE WHEN invoice_type = "membership" THEN 1 ELSE 0 END) as total_membership_sales,
            SUM(CASE WHEN payment_method = "momo" THEN 1 ELSE 0 END) as total_payment_method_momo,
            SUM(CASE WHEN payment_method = "vnpay" THEN 1 ELSE 0 END) as total_payment_method_vnpay
        ')
            ->where('status', 'Đã thanh toán')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC');
    }
}
