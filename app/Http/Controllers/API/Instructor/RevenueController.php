<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RevenueController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    const DOCUMENT_LESSON = 'documents/lessons';

    public function getTotalRevenueWithStudents()
    {
        try {
            $user = Auth::user();
            $data = [];

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập, vui lòng đăng nhập và thử lại');
            }

            $totalRevenue = Invoice::query()
                ->where('status', 'Đã thanh toán')
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('courses')
                        ->whereRaw('courses.id = invoices.course_id')
                        ->where('courses.user_id', $user->id);
                })
                ->sum('final_amount');

            $userBuyCourse = Invoice::query()
                ->select('id', 'user_id')
                ->where('status', 'Đã thanh toán')
                ->with('user:id,name,avatar')
                ->get();

            $data['total_revenue'] = $totalRevenue;
            $data['user_buy_course'] = $userBuyCourse;

            return $this->respondOk('Doanh thu và học viên mua khóa học của giảng viên ' . $user->name, $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
