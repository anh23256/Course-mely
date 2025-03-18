<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\BuyCourseRequest;
use App\Http\Requests\API\Transaction\DepositTransactionRequest;
use App\Mail\StudentCoursePurchaseMail;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Invoice;
use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\InstructorNotificationForCoursePurchase;
use App\Notifications\JoiFreeCourseNotification;
use App\Notifications\UserBuyCourseNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function enrollFreeCourse(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'course_id' => 'required|exists:courses,id',
            ]);

            $userId = Auth::id();
            $courseId = $request->course_id;

            if (!$userId) {
                return $this->respondForbidden('Vui lòng đăng nhập để tham gia khoá học');
            }

            $course = Course::query()->find($courseId);

            if (CourseUser::query()->where([
                'user_id' => $userId,
                'course_id' => $courseId,
            ])->exists()) {
                return $this->respondOk('Bạn đã đã tham gia khoá học này ồi');
            }

            if ($course->price_sale > 0 && $course->price > 0) {
                return $this->respondError('Khóa học không phải miễn phí');
            }

            CourseUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);

            $course->increment('total_student');

            $instructor = $course->user;

            $instructor->notify(
                new JoiFreeCourseNotification(Auth::user(), $course)
            );

            DB::commit();

            return $this->respondOk('Tham gia khoá học thành công');
        } catch (\Exception $e) {
            DB::rollBack();

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

    public function createPayment(Request $request)
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
                'payment_method' => 'required|string',
            ]);

            $courseId = $validated['course_id'];

            if (CourseUser::query()->where('user_id', $user->id)->where('course_id', $courseId)->exists()) {
                return $this->respondError('Bạn đã sở hữu khoá học này rồi');
            }

            $course = Course::query()->find($validated['course_id']);
            if (!$course) {
                return $this->respondError('Không tìm thấy khóa học');
            }

            $originalAmount = $course->price_sale > 0 ? $course->price_sale : $course->price;
            $finalAmount = $validated['amount'];
            $discountAmount = $originalAmount - $finalAmount;
            $couponCode = $validated['coupon_code'] ?? null;

            return match ($validated['payment_method']) {
                'momo' => $this->createMoMoPayment($user, $courseId, $originalAmount, $discountAmount, $finalAmount, $couponCode),
                'vnpay' => $this->createVnPayPayment($user, $courseId, $originalAmount, $discountAmount, $finalAmount, $couponCode),
                default => $this->respondError('Phương thức thanh toán không hợp lệ')
            };
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    protected function createVnPayPayment($user, $courseId, $originalAmount, $discountAmount, $finalAmount, $couponCode = null)
    {
        $amountVNPay = number_format($finalAmount, 0, '', '');

        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_ReturnUrl = config('vnpay.return_url');

        $vnp_TxnRef = 'ORDER' . time();
        $vnp_OrderInfo = $user->id . '-Thanh-toan-khoa-hoc-' . $courseId .
            '-' . $originalAmount . '-' . $discountAmount . '-' . $finalAmount;

        if (!empty($couponCode)) {
            $vnp_OrderInfo .= '-' . $couponCode;
        }

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amountVNPay * 100,
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = "";
        $hashdata = "";
        $i = 0;

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
    }

    protected function vnpayCallback(Request $request)
    {
        try {
            $vnp_HashSecret = config('vnpay.hash_secret');
            $frontendUrl = config('app.fe_url') . "/payment";

            $inputData = $request->all();
            if (!isset($inputData['vnp_SecureHash'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);

            $hashData = urldecode(http_build_query($inputData));
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash !== $vnp_SecureHash) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            // Nếu thanh toán không thành công
            if ($inputData['vnp_ResponseCode'] != '00') {
                return redirect()->away($frontendUrl . "?status=failed");
            }

            DB::beginTransaction();

            if (!isset($inputData['vnp_OrderInfo'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $orderParts = explode('-Thanh-toan-khoa-hoc-', $inputData['vnp_OrderInfo']);
            if (count($orderParts) != 2) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $userId = filter_var(trim($orderParts[0], '"'), FILTER_VALIDATE_INT);

            $remainingParts = explode('-', $orderParts[1]);
            if (count($remainingParts) < 4) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $courseId = filter_var(trim($remainingParts[0], '"'), FILTER_VALIDATE_INT);
            $originalAmount = filter_var(trim($remainingParts[1], '"'), FILTER_VALIDATE_FLOAT);
            $discountAmount = filter_var(trim($remainingParts[2], '"'), FILTER_VALIDATE_FLOAT);
            $finalAmount = filter_var(trim($remainingParts[3], '"'), FILTER_VALIDATE_FLOAT);
            $couponCode = count($remainingParts) > 4 ? trim($remainingParts[4], '"') : null;

            $user = User::query()->find($userId);
            $course = Course::query()->find($courseId);

            if (!$user) {
                return redirect()->away($frontendUrl . "/not-found");
            }

            if (!$course) {
                return redirect()->away($frontendUrl . "/not-found");
            }

            $discount = null;
            if (!empty($couponCode)) {
                $discount = Coupon::query()
                    ->where(['code' => $couponCode, 'status' => '1'])
                    ->first();
            }

            $invoice = Invoice::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'amount' => $originalAmount,
                'coupon_code' => $discount ? $discount->code : null,
                'coupon_discount' => $discountAmount,
                'final_amount' => $finalAmount,
                'status' => 'Đã thanh toán',
                'code' => Str::upper(Str::random(10)),
                'payment_method' => 'vnpay'
            ]);

            CourseUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);

            $transaction = Transaction::create([
                'transaction_code' => $inputData['vnp_TxnRef'],
                'user_id' => $userId,
                'amount' => $inputData['vnp_Amount'] / 100,
                'transactionable_id' => $invoice->id,
                'transactionable_type' => Invoice::class,
                'status' => 'Giao dịch thành công',
                'type' => 'invoice',
            ]);

            $this->finalBuyCourse($userId, $course, $transaction, $invoice, $discount, $finalAmount);

            DB::commit();

            return redirect()->away($frontendUrl . "?status=success");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->away($frontendUrl . "?status=error");
        }
    }

    protected function createMoMoPayment($user, $courseId, $originalAmount, $discountAmount, $finalAmount, $couponCode = null)
    {
        try {
            $endpoint = config('momo.endpoint');
            $partnerCode = config('momo.partner_code');
            $accessKey = config('momo.access_key');
            $secretKey = config('momo.secret_key');
            $returnUrl = config('momo.return_url');
            $ipnUrl = config('momo.notify_url', $returnUrl);
            $requestType = "payWithATM";
            $extraData = "";

            $orderId = 'ORDER' . time();
            $requestId = time() . "_" . uniqid();

            $orderInfo = $user->id . '-Thanh-toan-khoa-hoc-' . $courseId .
                '-' . $originalAmount . '-' . $discountAmount . '-' . $finalAmount;

            if (!empty($couponCode)) {
                $orderInfo .= '-' . $couponCode;
            }

            $finalAmount = (float)$finalAmount;

            $rawHash =
                "accessKey=" . $accessKey .
                "&amount=" . $finalAmount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $ipnUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $returnUrl .
                "&requestId=" . $requestId .
                "&requestType=" . $requestType;

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'requestId' => $requestId,
                'amount' => $finalAmount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $returnUrl,
                'ipnUrl' => $ipnUrl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
            ];

            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);

            if (isset($jsonResult['payUrl'])) {
                return $this->respondOk('Tạo link thanh toán MoMo thành công', $jsonResult['payUrl']);
            } else {
                return $this->respondServerError('Ngân hàng tạm thời bảo trì: ' . ($jsonResult['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra khi tạo thanh toán MoMo: ' . $e->getMessage());
        }
    }

    protected function momoCallback(Request $request)
    {
        try {
            $inputData = $request->all();
            $frontendUrl = config('app.fe_url') . "/payment";
            if (!isset($inputData['signature'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            if ($inputData['resultCode'] != '0') {
                Log::info(2);
                return redirect()->away($frontendUrl . "?status=failed");
            }

            DB::beginTransaction();

            if (!isset($inputData['orderInfo'])) {
                Log::info(3);
                return redirect()->away($frontendUrl . "?status=error");
            }

            $orderParts = explode('-Thanh-toan-khoa-hoc-', $inputData['orderInfo']);
            if (count($orderParts) != 2) {
                Log::info(4);
                return redirect()->away($frontendUrl . "?status=error");
            }

            $userId = filter_var(trim($orderParts[0], '"'), FILTER_VALIDATE_INT);

            $remainingParts = explode('-', $orderParts[1]);
            if (count($remainingParts) < 4) {
                Log::info(5);
                return redirect()->away($frontendUrl . "?status=error");
            }

            $courseId = filter_var(trim($remainingParts[0], '"'), FILTER_VALIDATE_INT);
            $originalAmount = filter_var(trim($remainingParts[1], '"'), FILTER_VALIDATE_FLOAT);
            $discountAmount = filter_var(trim($remainingParts[2], '"'), FILTER_VALIDATE_FLOAT);
            $finalAmount = filter_var(trim($remainingParts[3], '"'), FILTER_VALIDATE_FLOAT);
            $couponCode = count($remainingParts) > 4 ? trim($remainingParts[4], '"') : null;

            $user = User::query()->find($userId);
            $course = Course::query()->find($courseId);

            if (!$user) {
                Log::info(6);
                return redirect()->away($frontendUrl . "/not-found");
            }

            if (!$course) {
                Log::info(6);
                return redirect()->away($frontendUrl . "/not-found");
            }

            $discount = null;
            if (!empty($couponCode)) {
                $discount = Coupon::query()
                    ->where(['code' => $couponCode, 'status' => '1'])
                    ->first();
            }

            $invoice = Invoice::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'amount' => $originalAmount,
                'coupon_code' => $discount ? $discount->code : null,
                'coupon_discount' => $discountAmount,
                'final_amount' => $finalAmount,
                'status' => 'Đã thanh toán',
                'code' => Str::upper(Str::random(10)),
                'payment_method' => 'momo'
            ]);

            CourseUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);

            $transaction = Transaction::create([
                'transaction_code' => $inputData['orderId'],
                'user_id' => $userId,
                'amount' => $inputData['amount'],
                'transactionable_id' => $invoice->id,
                'transactionable_type' => Invoice::class,
                'status' => 'Giao dịch thành công',
                'type' => 'invoice',
            ]);

            $this->finalBuyCourse($userId, $course, $transaction, $invoice, $discount, $finalAmount);

            DB::commit();

            return redirect()->away($frontendUrl . "?status=success");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->away($frontendUrl . "?status=error");
        }
    }

    private function finalBuyCourse($userID, $course, $transaction, $invoice, $discount = null, $finalAmount = null)
    {
        if ($discount) {
            $discount->refresh();
            $discount->increment('used_count');

            if($discount->max_usage > 0){
                $discount->decrement('max_usage');
            }

            $couponUse = CouponUse::query()->where([
                'coupon_id' => $discount->id,
                'user_id' => $userID
            ]);

            $couponUse->update([
                'status' => 'used',
            ]);
        }

        $course->refresh();
        $course->increment('total_student');

        $walletInstructor = Wallet::query()
            ->firstOrCreate([
                'user_id' => $course->user_id
            ]);

        $walletInstructor->balance += $finalAmount * self::instructorRate;

        $walletInstructor->save();

        $walletWeb = Wallet::query()
            ->firstOrCreate([
                'user_id' => User::where('email', self::walletMail)
                    ->value('id'),
            ]);

        $walletWeb->balance += $finalAmount * self::adminRate;
        $walletWeb->save();

        $systemFund = SystemFund::query()->first();

        if ($systemFund) {
            $systemFund->balance += $finalAmount * self::adminRate;
            $systemFund->pending_balance += $finalAmount * self::instructorRate;
            $systemFund->save();
        } else {
            SystemFund::query()->create([
                'balance' => $finalAmount * self::adminRate,
                'pending_balance' => $finalAmount * self::instructorRate
            ]);
        }

        SystemFundTransaction::query()->create([
            'transaction_id' => $transaction->id,
            'course_id' => $course->id,
            'user_id' => $userID,
            'total_amount' => $finalAmount,
            'retained_amount' => $finalAmount * self::adminRate,
            'type' => 'commission_received',
            'description' => 'Tiền hoa hồng nhận được từ việc bán khóa học: ' . $course->name,
        ]);

        $instructor = $course->user;

        User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })
            ->each(fn($manager) => $manager->notify(
                new UserBuyCourseNotification(User::find($userID), $course->load('invoices.transaction'))
            ));

        $instructor->notify(
            new InstructorNotificationForCoursePurchase(
                User::find($userID),
                $course,
                $transaction
            )
        );

        $student = User::find($userID);

        Mail::to($student->email)->send(
            new StudentCoursePurchaseMail($student, $course, $transaction, $invoice)
        );
    }

    public function applyCoupon(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $data = $request->validate([
                'code' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'course_id' => 'nullable|integer|exists:courses,id',
            ]);

            return $this->checkCoupon($data['code'], $data['amount'], $data['course_id']);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    private function checkCoupon(?string $code, float $amount, $courseId = null)
    {
        if (empty($code)) {
            return [
                'original_amount' => $amount,
                'discount_amount' => 0,
                'final_amount' => $amount,
            ];
        }

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('status', '1')->first();

        if (!$coupon) {
            return $this->respondNotFound('Mã giảm giá không hợp lệ');
        }

        $couponAssigned = CouponUse::query()
            ->where('user_id', Auth::id())
            ->where('coupon_id', $coupon->id)
            ->first();

        if (!$couponAssigned) {
            return $this->respondError('Mã giảm giá không áp dụng cho tài khoản của bạn');
        }

        if ($couponAssigned->status === 'used') {
            return $this->respondError('Bạn đã sử dụng mã giảm giá này');
        }

        if (!is_null($coupon->max_usage) && $coupon->used_count >= $coupon->max_usage) {
            return $this->respondError('Mã giảm giá đã hết số lượt sử dụng');
        }

        if ($coupon->start_date && now()->lessThan($coupon->start_date)) {
            return $this->respondError('Mã giảm giá chưa được kích hoạt');
        }

        if ($coupon->specific_course) {
            if (is_null($courseId)) {
                return $this->respondError('Mã giảm giá này chỉ áp dụng cho khóa học cụ thể. Vui lòng cung cấp ID khóa học');
            }

            $isApplicable = $coupon
                ->couponCourses()
                ->where('course_id', $courseId)->exists();
            if (!$isApplicable) {
                return $this->respondError('Mã giảm giá này không áp dụng cho khóa học này');
            }
        }

        $discountAmount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($amount * $coupon->discount_value) / 100;

            if (!empty($coupon->discount_max_value)) {
                $discountAmount = min($discountAmount, $coupon->discount_max_value);
            }
        } elseif ($coupon->discount_type === 'fixed') {
            $discountAmount = min($coupon->discount_value, $amount);
        }

        $finalAmount = max($amount - $discountAmount, 0);

        return $this->respondOk('Áp dụng mã giảm giá thành công', [
            'original_amount' => $amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount
        ]);
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
