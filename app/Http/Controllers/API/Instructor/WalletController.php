<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
