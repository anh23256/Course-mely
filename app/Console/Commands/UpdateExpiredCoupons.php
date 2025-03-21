<?php

namespace App\Console\Commands;

use App\Models\Coupon;
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
        // Cập nhật mã giảm giá đã hết hạn
        $expiredCoupons = Coupon::where('expire_date', '<', Carbon::today())
                                ->where('status', 1)
                                ->update(['status' => 0]);

        // Cập nhật mã giảm giá hết lượt sử dụng
        $usedUpCoupons = Coupon::whereColumn('used_count', '>=', 'max_usage')
                               ->where('status', 1)
                               ->update(['status' => 0]);

        // Hiển thị thông tin ra console
        $this->info("Đã cập nhật {$expiredCoupons} mã giảm giá hết hạn.");
        $this->info("Đã cập nhật {$usedUpCoupons} mã giảm giá hết lượt sử dụng.");
    }
}
