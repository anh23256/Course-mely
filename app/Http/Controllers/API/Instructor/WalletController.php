<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getWallet()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->where('status', 1)
                ->first();

            if (!$wallet) {
                return $this->respondNotFound('Không tìm thấy ví');
            }

            return $this->respondOk('Thông tin ví của: ' . $user->name, $wallet);
        } catch (\Exception $e) {
            $this->logException($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function withDrawRequest(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->where('status', 1)
                ->lockForUpdate()
                ->first();

            $data = $request->validate([
                'account_no' => 'nullable|numeric',
                'account_name' => 'nullable|string',
                'acq_id' => 'nullable',
                'amount' => 'nullable|numeric',
                'add_info' => 'nullable|string',
                'bank_name' => 'required|string',
            ]);

            if ($data['amount'] > $wallet->balance) {
                return $this->respondError('Số dư không đủ để thực hiện yêu cầu, vui lòng kiểm tra lại');
            }

            $response = \Illuminate\Support\Facades\Http::post('https://api.vietqr.io/v2/generate', [
                'accountNo' => $data['account_no'],
                'accountName' => $data['account_name'],
                'acqId' => $data['acq_id'],
                'amount' => $data['amount'],
                'addInfo' => $data['add_info'] ?? '',
                'template' => 'pwMusbq',
            ]);

            if ($response->failed()) {
                return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
            }

            $responseBody = $response->json();

            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $responseBody['data']['qrDataURL']));
            $filePath = 'qr/' . uniqid() . '.png';
            Storage::disk('public')->put($filePath, $imageData);

            $withdrawalRequest = WithdrawalRequest::query()->create([
                'wallet_id' => $wallet->id,
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_no'],
                'account_holder' => $data['acq_id'],
                'amount' => $data['amount'],
                'note' => $data['add_info'] ?? '',
                'qr_code' => $filePath,
                'status' => 'Đang xử lý',
                'request_date' => now(),
            ]);

            $user->wallet->decrement('balance', $data['amount']);

            DB::commit();

            $managers = User::query()
                ->with('role', function ($query) {
                    $query->where('name', 'admin');
                })
                ->where('email', 'quaixe121811@gmail.com')
                ->first();

            $managers->notify(new WithdrawalNotification($withdrawalRequest, $user));

            return $this->respondCreated('Gửi yêu cầu thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại sau'
            );
        }
    }

    public function getWithdrawalRequests(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $query = WithdrawalRequest::query()
                ->whereHas('wallet.user', function ($query) {
                    $query->whereColumn('users.id', 'wallets.user_id');
                })
                ->with('wallet.user');

            if ($request->has('fromDate')) {
                $query->whereDate('created_at', '>=', $request->input('fromDate'));
            }

            if ($request->has('toDate')) {
                $query->whereDate('created_at', '<=', $request->input('toDate'));
            }

            $withdrawalRequests = $query->get();

            if ($withdrawalRequests->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            return $this->respondOk('Danh sách yêu cầu của: ' . $user->name, $withdrawalRequests);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getWithDrawRequest(string $id)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Vui lòng đăng nhập');
            }

            $withdrawalRequest = WithdrawalRequest::query()
                ->whereHas('wallet.user', function ($query) {
                    $query->whereColumn('users.id', 'wallets.user_id');
                })
                ->with('wallet.user')
                ->find($id);

            if (!$withdrawalRequest) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            return $this->respondOk('Danh sách yêu cầu của: ' . $user->name, $withdrawalRequest);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function handleConfirmWithdrawal(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $data = $request->validate([
                'is_received' => 'nullable|in:0,1',
                'instructor_confirmation_note' => 'required_if:instructor_confirmation,confirmed',
            ]);

            $withdrawalRequest = WithdrawalRequest::query()
                ->with('wallet.user')
                ->find($id);

            if (!$withdrawalRequest) {
                return $this->respondNotFound('Không tìm thấy giao dịch');
            }

            $status = $data['is_received'] == 1 ? 'Chờ xử lý' : 'Hoàn thành';

            $withdrawalRequest->update([
                'instructor_confirmation' => 'confirmed',
                'instructor_confirmation_note' => $data['instructor_confirmation_note'] ?? null,
                'instructor_confirmation_date' => now(),
                'is_received' => $data['is_received'] ?? 0,
                'status' => $status,
            ]);

            $message = $data['is_received'] == 1
                ? 'Yêu cầu khiếu lại của bạn đã được gửi đi!'
                : 'Yêu cầu đã được xử lý thành công!';

            DB::commit();

            return $this->respondOk($message);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
