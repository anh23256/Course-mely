<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\BuyCourseRequest;
use App\Http\Requests\API\Transaction\DepositTransactionRequest;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Invoice;
use App\Models\SystemFund;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\UserBuyCourseNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Return_;

class TransactionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    const adminRate = 0.4;
    const instructorRate = 1 - self::adminRate;
    const walletMail = 'quaixe121811@gmail.com';

    public function index()
    {
        try {
            $transactions = Transaction::query()->where('transactionable_id', Auth::id())->latest('id')->get();

            return $this->respondOk('Danh sách giao dịch của: ' . Auth::user()->name, $transactions);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function show(string $id)
    {
        try {
            $transaction = Transaction::query()->findOrFail($id);

            return $this->respondOk('Chi tiết giao dịch của: ' . Auth::user()->name, $transaction);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function deposit(DepositTransactionRequest $request)
    {
        try {
            $data = $request->validated();

            $deposit = Transaction::query()->create([
                'amount' => $request->amount,
                'coin' => round($request->amount / 1000, 2),
                'transactionable_id' => Auth::id(),
                'transactionable_type' => 'App\Models\User',
            ]);

            return response()->json([
                'message' => 'Giao dịch nạp tiền đang chờ xử lý',
                'deposit' => $deposit,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Nạp tiền thất bại, vui lòng thử lại',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createVNPayPayment(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Vui lòng đăng nhập để mua khóa học');
            }

            $validated = $request->validate([
                'amount' => 'required|numeric',
                'course_id' => 'required|exists:courses,id',
                'coupon_code' => 'nullable|string',
            ]);

            $amount = number_format($validated['amount'], 0, '', '');

            $vnp_TmnCode = env('VNPAY_TMN_CODE');
            $vnp_HashSecret = env('VNPAY_HASH_SECRET');
            $vnp_Url = env('VNPAY_URL');
            $vnp_ReturnUrl = env('VNPAY_RETURN_URL');

            $vnp_TxnRef = 'ORDER' . time();
            $vnp_OrderInfo = $user->id . '-Thanh-toan-khoa-hoc-' . $validated['course_id'];
            $vnp_Amount = $amount * 100;
            if (!empty($validated['coupon_code'])) {
                $vnp_OrderInfo .= '-' . $validated['coupon_code'];
            }
            $vnp_Locale = 'vn';
            $vnp_IpAddr = request()->ip();

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_Command" => "pay",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_CreateDate" => now()->format('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => "billpayment",
                "vnp_ReturnUrl" => $vnp_ReturnUrl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";

            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return $this->respondOk('Tạo link thanh toán thành công', $vnp_Url);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function vnpayCallback(Request $request)
    {
        try {
            $vnp_HashSecret = env('VNPAY_HASH_SECRET');
            $frontendUrl = env('FE_URL') . "my-courses";

            $inputData = $request->all();
            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);

            $hashData = urldecode(http_build_query($inputData));
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash !== $vnp_SecureHash) {
                return redirect()->away($frontendUrl . "?error=invalid_hash");
            }

            // Nếu thanh toán không thành công
            if ($inputData['vnp_ResponseCode'] != '00') {
                return redirect()->away($frontendUrl . "?vnp_ResponseCode=" . $inputData['vnp_ResponseCode']);
            }

            DB::beginTransaction();

            // Giả sử vnp_OrderInfo chứa user_id và course_id
            $orderInfo = explode('-', str_replace('-Thanh-toan-khoa-hoc-','-',$inputData['vnp_OrderInfo']));

            $userId = trim($orderInfo[0], '"');
            $courseId = trim($orderInfo[1], '"');

            $user = User::find($userId);
            $course = Course::find($courseId);

            if (!$user || !$course) {
                return redirect()->away($frontendUrl . "?error=invalid_data");
            }

            // Kiểm tra mã giảm giá (nếu có)
            $discount = null;
            if (!empty(trim($orderInfo[2], '"'))) {
                $discount = Coupon::where(['code' => trim($orderInfo[2], '"'), 'status' => '1'])->first();
            }

            // Tạo hóa đơn (invoice)
            $invoice = Invoice::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'amount' => $inputData['vnp_Amount'] / 100,
                'status' => 'Đã thanh toán',
                'coupon_code' => $discount ? $discount->code : null,
            ]);

            // Thêm học viên vào khóa học
            CourseUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);

            // Lưu giao dịch
            $transaction = Transaction::create([
                'transaction_code' => $inputData['vnp_TxnRef'],
                'user_id' => $userId,
                'amount' => $inputData['vnp_Amount'] / 100,
                'transactionable_id' => $invoice->id,
                'transactionable_type' => Invoice::class,
                'status' => 'Giao dịch thành công',
                'type' => 'invoice',
            ]);

            $this->finalBuyCourse($userId, $course, $transaction, $discount, $inputData['vnp_Amount']/100);

            DB::commit();

            return redirect()->away(env('FE_URL') . "my-courses?vnp_TxnRef=" . $inputData['vnp_TxnRef']);
        } catch (\Exception $e) {
            Log::error("VNPAY Callback Error: " . $e);
            DB::rollBack();
            return redirect()->away($frontendUrl . "?error=server_error");
        }
    }

    public function buyCourse(BuyCourseRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $userID = Auth::id();

            if (!$userID) {
                return $this->respondUnAuthenticated('Vui lòng đăng nhập để mua khóa học');
            }

            $course = Course::query()->where([
                'slug' => $request->slug,
                'status' => 'approved'
            ])->first();

            if (!$course) {
                return $this->respondError('Không tìm thấy khóa học');
            }

            if (CourseUser::where([
                'user_id' => $userID,
                'course_id' => $course->id,
            ])->exists()) {
                return $this->respondError('Bạn đã mua khóa học này rồi');
            }

            $discountAmount = 0;
            $discount = null;

            if (!empty($validated['coupon_code'])) {
                $discount = Coupon::query()->where([
                    'code' => $validated['coupon_code'],
                    'status' => '1'
                ])->first();

                if (!empty($discount)) {
                    $discountAmount = ($discount->discount_type === 'percentage')
                        ? (!empty(round($course->price_sale,2)) ? $course->price_sale :  $course->price) * $discount->discount_value / 100
                        : $discount->discount_value;

                    $discountAmount = min($discountAmount, !empty(round($course->price_sale,2)) ? $course->price_sale :  $course->price);
                } else {
                    return $this->respondError('Mã giảm giá không hợp lệ hoặc đã hết hạn');
                }
            }

            $finalAmount = max($validated['amount'] - $discountAmount, 0);

            if ($finalAmount === 0) {
                $invoice = Invoice::create([
                    'user_id' => $userID,
                    'course_id' => $course->id,
                    'code' => 'HD' . strtoupper(Str::random(8)),
                    'coupon_code' => $validated['coupon_code'] ?? null,
                    'coupon_discount' => $discountAmount > 0 ? $discountAmount : null,
                    'total' => $validated['amount'],
                    'final_total' => $finalAmount,
                    'status' => 'Đã thanh toán',
                ]);

                $transaction = Transaction::create([
                    'transaction_code' => 'GD' . strtoupper(Str::random(8)),
                    'user_id' => $userID,
                    'amount' => $validated['amount'],
                    'transactionable_id' => $invoice->id,
                    'transactionable_type' => Invoice::class,
                    'status' => 'Giao dịch thành công',
                    'type' => 'invoice',
                ]);

                CourseUser::create([
                    'user_id' => $userID,
                    'course_id' => $course->id,
                    'enrolled_at' => now(),
                ]);

                $this->finalBuyCourse($userID, $course, $transaction, $discount);

                DB::commit();

                return $this->respondOk('Mua khóa học thành công');
            } else {
                DB::commit();
                $payment_method = !empty($request->payment_method) ? $request->payment_method : 'vnpay';

                if ($payment_method === 'bank') {
                    return $this->respondOk('Chưa có bank');
                } else {
                    $modifiedRequest = $request->merge([
                        'amount' => $finalAmount,
                        'course_id' => $course->id
                    ]);

                    return $this->createVNPayPayment($modifiedRequest);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function finalBuyCourse($userID, $course, $transaction, $discount = null, $finalAmount = null)
    {
        if ($discount) {
            $course->coupons()->attach($discount->id);
            if ($discount->used_count > 0) $discount->decrement('used_count');
        }

        $course->increment('total_student');

        $walletInstructor = Wallet::query()->firstOrCreate(['user_id' => $course->user_id]);

        $walletInstructor->balance += $finalAmount * self::instructorRate;

        $walletInstructor->save();

        $walletWeb = Wallet::query()->firstOrCreate([
            'user_id' => User::where('email', self::walletMail)->value('id'),
        ]);

        $walletWeb->balance += $finalAmount * self::adminRate;

        $walletWeb->save();

        SystemFund::query()->create([
            'transaction_id' => $transaction->id,
            'course_id' => $course->id,
            'user_id' => $userID,
            'total_amount' => $finalAmount,
            'retained_amount' => $finalAmount * self::adminRate,
            'type' => 'commission_received',
            'description' => 'Tiền hoa hồng nhận được từ việc bán khóa học: ' . $course->name,
        ]);

        User::whereHas('roles', function($query){
            $query->where('name', 'admin');
        })
        ->each(fn($manager) => $manager->notify(
            new UserBuyCourseNotification(User::find($userID), $course->load('invoices.transaction'))
        ));
    }
}
