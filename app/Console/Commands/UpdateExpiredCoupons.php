<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\CouponUse;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateExpiredCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupons:update-expired-coupons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra mã giảm giá hết hạn hoặc đã dùng hết lượt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lấy danh sách mã giảm giá hết hạn
        $expiredCouponIds = Coupon::where('expire_date', '<', Carbon::today())
            ->where('status', 1)
            ->pluck('id');

        // Cập nhật trạng thái mã giảm giá hết hạn
        $expiredCoupons = Coupon::whereIn('id', $expiredCouponIds)
            ->update(['status' => 0]);

        // Cập nhật trạng thái trong bảng coupon_uses cho các mã giảm giá hết hạn
        CouponUse::whereIn('coupon_id', $expiredCouponIds)
            ->update(['status' => 'expired']);

        $usedUpCouponIds = Coupon::whereColumn('used_count', '>=', 'max_usage')
            ->where('status', 1)
            ->pluck('id');

        // Cập nhật trạng thái mã giảm giá hết lượt sử dụng
        $usedUpCoupons = Coupon::whereIn('id', $usedUpCouponIds)
            ->update(['status' => 0]);

        // Cập nhật trạng thái trong bảng coupon_uses cho các mã giảm giá hết lượt sử dụng
        CouponUse::whereIn('coupon_id', $usedUpCouponIds)
            ->update(['status' => 'expired']);

        // Hiển thị thông tin ra console
        $this->info("Đã cập nhật {$expiredCoupons} mã giảm giá hết hạn.");
        $this->info("Đã cập nhật trạng thái liên quan cho các mã giảm giá hết hạn.");
        $this->info("Đã cập nhật {$usedUpCoupons} mã giảm giá hết lượt sử dụng.");
        $this->info("Đã cập nhật trạng thái liên quan cho các mã giảm giá hết lượt sử dụng.");
    }
}
