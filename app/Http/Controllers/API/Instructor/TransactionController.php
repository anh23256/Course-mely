<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getParticipatedCourses()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $courses = Course::query()
                ->where('user_id', $user->id)->pluck('id')
                ->toArray();

            if (empty($courses)) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $paidTransactions = Transaction::where('transactionable_type', Invoice::class)
                ->where('amount', '>', 0)
                ->whereIn('transactionable_id', function ($query) use ($courses) {
                    $query->select('id')
                        ->from('invoices')
                        ->whereIn('course_id', $courses);
                })
                ->with(['user', 'transactionable.course'])
                ->get();

            $result = $paidTransactions->map(function ($transaction) {
                $invoice = $transaction->transactionable;

                return [
                    'course_thumbnail' => optional($invoice->course)->thumbnail,
                    'course_name' => optional($invoice->course)->name,
                    'student_name' => $transaction->user->name,
                    'amount_paid' => $transaction->amount,
                    'invoice_code' => $invoice->code,
                    'invoice_created_at' => $invoice->created_at,
                    'id' => $invoice->id,
                    'status' => $invoice->status,
                ];
            });

            return $this->respondOk('Danh sách khoá học đã bán của: ' . $user->name, $result);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getCourseEnrollFree()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $courses = Course::query()
                ->where('user_id', $user->id)->pluck('id')
                ->toArray();

        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
