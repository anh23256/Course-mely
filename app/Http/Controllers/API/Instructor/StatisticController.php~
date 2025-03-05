<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StatisticController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getCourseOverview()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $totalCourse = Course::query()->where('user_id', $user->id)->count();

            $totalEnrollments = DB::table('course_users')
                ->join('courses', 'course_users.course_id', '=', 'courses.id')
                ->where(['courses.user_id' => $user->id, 'courses.status' => 'approved'])
                ->count();

            $totalRevenue = DB::table('invoices')->selectRaw('SUM(final_amount) as total_revenue')->where('invoices.status',  "Đã thanh toán")
                ->join('courses', 'invoices.course_id', '=', 'courses.id')
                ->where(['courses.user_id' => $user->id, 'courses.status' => 'approved'])
                ->first();

            $averageRating = DB::table('ratings')->selectRaw('ROUND(AVG(ratings.rate), 1) as avg_rating')
                ->join('courses', 'ratings.course_id', '=', 'courses.id')
                ->where(['courses.user_id' => $user->id, 'courses.status' => 'approved'])->first();

            return $this->respondOk('Dữ liệu thông kê tổng quan của giảng viên ' . $user->name, [
                'totalCourse' => $totalCourse,
                'totalEnrollments' => $totalEnrollments,
                'totalRevenue' => $totalRevenue->total_revenue,
                'averageRating' => $averageRating->avg_rating
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Lấy dữ liệu thống kê tổng quan của giảng viên không thành công');
        }
    }

    public function getCourseRevenue(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $courseRevenue = DB::table('invoices')
                ->select(
                    'courses.id',
                    'courses.name',
                    'courses.price',
                    'courses.price_sale',
                    'courses.slug',
                    DB::raw('SUM(invoices.final_amount) as total_revenue'),
                    DB::raw('COUNT(DISTINCT course_users.id) as total_student'),
                    DB::raw('ROUND(AVG(course_users.progress_percent),2) as avg_progress'),
                    DB::raw('ROUND(AVG(DISTINCT ratings.rate), 1) as avg_rating')
                )
                ->join('courses', 'invoices.course_id', '=', 'courses.id')
                ->join('course_users', 'courses.id', '=', 'course_users.course_id')
                ->leftJoin('ratings', 'courses.id', '=', 'ratings.course_id')
                ->where([
                    'courses.user_id' => $user->id,
                    'invoices.status' => 'Đã thanh toán'
                ])
                ->groupBy('courses.slug')
                ->orderByDesc('total_revenue')
                ->get();

            return $this->respondOk('Doanh thu khóa học của giảng viên ' . $user->name, [
                'courseRevenue' => $courseRevenue
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getMonthlyRevenue(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $yearNow = now()->year;

            $startDate = $request->input('start_date', '');
            $endDate = $request->input('end_date', now());
            $now = now();
            $userCreatedAt = Carbon::parse($user->created_at);

            if (!empty($startDate)) {
                try {
                    $startDate = Carbon::parse($startDate);
                    $endDate = Carbon::parse($endDate);
                } catch (\Exception $e) {
                    return $this->respondError('Định dạng ngày không hợp lệ');
                }

                if ($startDate->greaterThan($endDate)) {
                    return $this->respondError('Ngày bắt đầu không được lớn hơn ngày kết thúc');
                }

                if ($startDate->lessThan($userCreatedAt)) {
                    $startDate = $userCreatedAt;
                }

                if ($endDate->greaterThan($now)) {
                    $endDate = $now;
                }
            } else {
                $startDate = Carbon::create($yearNow, 1, 1);
                $endDate = now();
            }

            $startDate = $startDate->format('Y-m-d 00:00:00');
            $endDate = $endDate->format('Y-m-d 23:59:59');

            $monthlyRevenue = DB::table('invoices')
                ->selectRaw('DATE_FORMAT(invoices.created_at, "%Y-%m") as month, ROUND(SUM(final_amount) * 0.6, 2) as revenue')
                ->join('courses', 'invoices.course_id', '=', 'courses.id')
                ->where([
                    'invoices.status' => 'Đã thanh toán',
                    'courses.user_id' => $user->id
                ])
                ->whereBetween('invoices.created_at', [$startDate, $endDate])
                ->groupBy('month')
                ->pluck('revenue', 'month')
                ->toArray();

            $allMonths = [];
            $current = Carbon::parse($startDate)->startOfMonth();
            $last = Carbon::parse($endDate)->startOfMonth();

            while ($current <= $last) {
                $allMonths[$current->format('Y-m')] = null;
                $current->addMonth();
            }

            $completeMonthlyRevenue = array_replace($allMonths, $monthlyRevenue);

            return $this->respondOk('Doanh thu theo tháng của giảng viên ' . $user->name, [
                'monthlyRevenue' => $completeMonthlyRevenue,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getRatingStats()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $courseIds = Course::query()->where('user_id', $user->id)->pluck('id');

            if ($courseIds->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu rating cho các khóa học.');
            }

            $ratingStats = Rating::query()->whereIn('course_id', $courseIds)
                ->selectRaw('rate, COUNT(*) as count')
                ->groupBy('rate')
                ->get();

            $totalRatings = $ratingStats->sum('count');

            $data = $ratingStats->map(function ($stat) use ($totalRatings) {
                return [
                    'rate' => $stat->rate,
                    'count' => $stat->count,
                    'percentage' => $totalRatings > 0
                        ? round(($stat->count / $totalRatings) * 100, 2)
                        : 0,
                ];
            });

            return $this->respondOk('Bao cáo tỷ lệ đánh giá', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
}
