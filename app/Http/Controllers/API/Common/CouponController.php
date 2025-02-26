<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    use LoggableTrait;
    public function acceptCoupon($couponId)
    {
        $coupon = Coupon::findOrFail($couponId);
        $applied_at = Coupon::with('CouponUse')->findOrFail($couponId);
        $existingCouponUser = CouponUse::where('coupon_id', $couponId)
                                        ->where('user_id', Auth::id())
                                        ->first();

        if ($existingCouponUser) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn đã nhận mã giảm giá này'
            ]);
        }

        // Lưu vào bảng coupon_users
        CouponUse::create([
            'coupon_id' => $couponId,
            'user_id' => Auth::id(),
            'status' =>'unused',
            'applied_at'=> $applied_at->start_date,
            'expired_at'=> $applied_at->expired_at
        ]);

        // Redirect hoặc thông báo thành công
        return  response()->json([
            'status' => true,
            'message' => 'Bạn đã nhận mã giảm giá thành công'
        ]);
    }
}
