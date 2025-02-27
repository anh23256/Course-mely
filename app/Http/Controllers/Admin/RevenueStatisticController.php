<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Cloudinary\Transformation\FillTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueStatisticController extends Controller
{
    use FillTrait;
    public function index(Request $request)
    {
        $title = 'Thống kê doanh thu';
        $year = now()->year;

        $totalAmount = DB::table('system_fund_transactions')
            ->select(
                DB::raw('SUM(total_amount) as totalRevenue'),
                DB::raw('SUM(retained_amount) as totalProfit'),
            )
            ->where('type', 'commission_received')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.id', 'system_fund_transactions.transaction_id')
                    ->where('transactions.type', 'invoice')
                    ->where('transactions.status', 'Giao dịch thành công');
            })->whereYear('created_at', $year)
            ->first();

        $totalCourse = Course::query()
            ->where('status', 'approved')
            ->count();
        $totalInstructor = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'instructor');
            })
            ->count();

        $queryTopInstructors = User::query()
            ->join('courses', 'users.id', '=', 'courses.user_id')
            ->join('invoices', 'courses.id', '=', 'invoices.course_id')
            ->join('course_users', 'courses.id', '=', 'course_users.course_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'instructor');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                'users.created_at',
                DB::raw('SUM(invoices.final_amount) as total_revenue'),
                DB::raw('COUNT(courses.id) as total_courses'),
                DB::raw('COUNT(DISTINCT course_users.user_id) as total_enrolled_students'),
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar', 'users.created_at')
            ->orderBy('total_revenue', 'desc')
            ->limit(10);

        $queryTopUsers = User::query()
            ->join('invoices', 'users.id', '=', 'invoices.user_id')
            ->join('course_users', 'users.id', '=', 'course_users.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                DB::raw('COUNT(DISTINCT invoices.course_id) as total_courses_purchased'),
                DB::raw('SUM(invoices.final_amount) as total_spent'),
                DB::raw('MAX(invoices.created_at) as last_purchase_date')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->having('total_courses_purchased', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(10);

        $queryTopCourses = Course::query()
            ->join('invoices', 'courses.id', '=', 'invoices.course_id')
            ->join('course_users', 'courses.id', '=', 'course_users.course_id')
            ->select(
                'courses.id',
                'courses.name',
                'courses.thumbnail',
                'courses.created_at',
                DB::raw('SUM(invoices.final_amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT course_users.user_id) as total_enrolled_students'),
                DB::raw('COUNT(invoices.id) as total_sales'),
            )
            ->groupBy('courses.id', 'courses.name', 'courses.thumbnail', 'courses.created_at')
            ->orderByDesc('total_revenue')
            ->limit(10);

        $querySystem_Funds = DB::table('system_fund_transactions')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(retained_amount) as total_profit')
            )
            ->where('type', 'commission_received')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.id', 'system_fund_transactions.transaction_id')
                    ->where('transactions.type', 'invoice')
                    ->where('transactions.status', 'Giao dịch thành công');
            })
            ->groupBy(DB::raw('MONTH(created_at), YEAR(created_at)'))
            ->orderBy('year')->orderBy('month');

        $querySumRevenueProfit = DB::table('system_fund_transactions')
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(retained_amount) as total_profit')
            )
            ->where('type', 'commission_received')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.id', 'system_fund_transactions.transaction_id')
                    ->where('transactions.type', 'invoice')
                    ->where('transactions.status', 'Giao dịch thành công');
            });

        $queryCourseRatings = DB::table(DB::raw('(SELECT course_id, FLOOR(AVG(rate)) as rating FROM ratings GROUP BY course_id) as subquery'))
            ->join('courses', 'subquery.course_id', '=', 'courses.id')
            ->selectRaw('rating, COUNT(course_id) as total_courses')
            ->groupBy('rating')
            ->orderBy('rating', 'desc');

        list($queryTopInstructors, $queryTopUsers, $queryTopCourses, $queryCourseRatings, $querySystem_Funds, $querySumRevenueProfit) = $this->getFilterDataChart($request, $queryTopInstructors, $queryTopUsers, $queryTopCourses, $queryCourseRatings, $querySystem_Funds, $querySumRevenueProfit);

        $topInstructors = $queryTopInstructors->paginate(5);

        $topUsers = $queryTopUsers->paginate(5);

        $topCourses = $queryTopCourses->paginate(5);

        $courseRatings = $queryCourseRatings->get();

        $system_Funds =  $querySystem_Funds->get();

        $sumRevenueProfit = $querySumRevenueProfit->first();

        if ($request->ajax()) {
            return response()->json([
                'top_courses_table' => view('revenue-statistics.includes.top_courses', compact('topCourses'))->render(),
                'top_instructors_table' => view('revenue-statistics.includes.top_instructors', compact('topInstructors'))->render(),
                'top_users_table' => view('revenue-statistics.includes.top_users', compact('topUsers'))->render(),
                'pagination_links_courses' => $topCourses->links()->toHtml(),
                'pagination_links_instructors' => $topInstructors->links()->toHtml(),
                'pagination_links_users' => $topUsers->links()->toHtml(),
                'apexCharts' => $system_Funds,
                'sumRevenueProfit' => $sumRevenueProfit,
                'course_rating' => $courseRatings,
            ]);
        }

        return view('revenue-statistics.index', compact([
            'title',
            'totalAmount',
            'totalCourse',
            'totalInstructor',
            'topInstructors',
            'topCourses',
            'topUsers',
            'system_Funds',
            'sumRevenueProfit',
            'courseRatings'
        ]));
    }

    private function getFilterDataChart(Request $request, $queryTopInstructors, $queryTopUsers, $queryTopCourses,  $queryCourseRatings, $querySystem_Funds, $querySumRevenueProfit)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $filter = $request->input('filter');
        $year = now()->year;

        if (!empty($startDate) && !empty($endDate) && !empty($filter)) {
            if ($filter == "totalRevenueCourseMely") {
                $querySystem_Funds->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);
                $querySumRevenueProfit->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);
            } else if ($filter == "topInstructorCourseMely") {
                $queryTopInstructors->where('invoices.created_at', '>=', $startDate)->where('invoices.created_at', '<=', $endDate);
            } else if ($filter == "topCourseBoughtCourseMely") {
                $queryTopCourses->where('invoices.created_at', '>=', $startDate)->where('invoices.created_at', '<=', $endDate);
            } else if ($filter == "topRatingCourseMely") {
                $queryCourseRatings->where('courses.created_at', '>=', $startDate)->where('courses.created_at', '<=', $endDate);
            } else if ($filter == "topStudentCourseMely") {
                $queryTopUsers->where('invoices.created_at', '>=', $startDate)->where('invoices.created_at', '<=', $endDate);
            }
        } else {
            $queryTopInstructors->whereYear('invoices.created_at', $year);

            $queryTopUsers->whereYear('invoices.created_at', $year);

            $queryCourseRatings->whereYear('courses.created_at', $year);

            $querySystem_Funds->whereYear('created_at', $year);

            $querySumRevenueProfit->whereYear('created_at', $year);
        }

        return [$queryTopInstructors, $queryTopUsers, $queryTopCourses, $queryCourseRatings, $querySystem_Funds, $querySumRevenueProfit];
    }
}
