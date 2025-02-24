<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StatisticController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    public function getTotalRevenueWithStudents(Request $request)
    {
        try {
            $user = Auth::user();
            $data = [];
            $yearNow = now()->year();
            $year = $request->input('year', $yearNow);

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập, vui lòng đăng nhập và thử lại');
            }

            if ($year > $yearNow) $year = $yearNow;
            if ($year < $user->created_at) $year = $user->created_at;

            $totalRevenue = Invoice::query()
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(final_amount) as total_revenue'),
                )
                ->where('status', 'Đã thanh toán')
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('courses')
                        ->whereRaw('courses.id = invoices.course_id')
                        ->where('courses.user_id', $user->id);
                })
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('month')->whereYear('created_at', $year)->get();

            $userBuyCourse = Invoice::query()
                ->select('user_id', 'course_id')
                ->where('status', 'Đã thanh toán')
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('courses')
                        ->whereRaw('courses.id = invoices.course_id')
                        ->where('courses.user_id', $user->id);
                })
                ->with(['user:id,name,avatar', 'course:id,name,slug'])
                ->whereYear('created_at', $year)
                ->get();

            $topCourse = Invoice::query()
                ->select(
                    'course_id',
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(id) as total_bought_course')
                )
                ->where('status', 'Đã thanh toán')
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('courses')
                        ->whereRaw('courses.id = invoices.course_id')
                        ->where('courses.user_id', $user->id);
                })
                ->with(['course:id,name,slug'])
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('MONTH(created_at)'), 'course_id')
                ->orderBy(DB::raw('MONTH(created_at)'))
                ->get();

            $data['total_revenue'] = $totalRevenue;
            $data['user_buy_course'] = $userBuyCourse;
            $data['topCourse'] = $topCourse;

            return $this->respondOk('Doanh thu và học viên mua khóa học của giảng viên ' . $user->name, $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
