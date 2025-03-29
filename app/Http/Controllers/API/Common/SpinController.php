<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Gift;
use App\Models\Spin;
use App\Models\SpinConfig;
use App\Models\SpinHistory;
use App\Models\SpinSetting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SpinController extends Controller
{
    // API kiểm tra trạng thái vòng quay
    public function getSpinStatus(Request $request)
    {
        $spinSetting = SpinSetting::first();
        if (!$spinSetting) {
            return response()->json([
                'status' => 'inactive',
                'message' => 'Vòng quay chưa được cấu hình!'
            ], 503); // 503 Service Unavailable
        }

        return response()->json([
            'status' => $spinSetting->status,
            'message' => $spinSetting->status === 'active' 
                ? 'Vòng quay đang hoạt động' 
                : 'Vòng quay đang bảo trì, vui lòng quay lại sau'
        ]);
    }
    // Lấy danh sách phần thưởng từ coupons, gifts và thêm phần thưởng mặc định
    private function getAllRewards()
{
    // Lấy từ SpinConfig và chia "Mã giảm giá ngẫu nhiên" thành 4 ô
    $rewards = SpinConfig::all()->flatMap(function ($config) {
        if ($config->name === 'Mã giảm giá') {
            $couponProbability = $config->probability / 4; // Chia đều cho 4 ô
            $couponRewards = [];
            for ($i = 1; $i <= 4; $i++) {
                $couponRewards[] = [
                    'type' => 'coupon',
                    'id' => null,
                    'name' => "Mã giảm giá $i",
                    'probability' => $couponProbability,
                ];
            }
            return $couponRewards;
        }

        return [[
            'type' => $config->type,
            'id' => null,
            'name' => $config->name,
            'probability' => $config->probability,
        ]];
    })->toArray();

    // Lấy từ bảng gifts (chỉ lấy nếu còn hàng)
    $gifts = Gift::where('stock', '>', 0)
        ->where('is_selected', 1)
        ->where('is_active', 1)
        ->limit(2)
        ->get();
    foreach ($gifts as $gift) {
        $rewards[] = [
            'type' => 'gift',
            'id' => $gift->id,
            'name' => $gift->name,
            'probability' => $gift->probability,
            'stock' => $gift->stock,
        ];
    }

    return $rewards;
}
    // Thuật toán Weighted Random Selection
    private function getRandomReward()
    {
        $rewards = $this->getAllRewards();

        if (empty($rewards)) {
            return [
                'type' => 'no_reward',
                'id' => null,
                'name' => 'Chúc bạn may mắn lần sau',
            ];
        }

        $total = array_sum(array_column($rewards, 'probability'));
        $random = mt_rand(0, $total * 100) / 100;

        $cumulative = 0;
        foreach ($rewards as $reward) {
            $cumulative += $reward['probability'];
            if ($random <= $cumulative) {
                return $reward;
            }
        }

        return $rewards[0]; // Mặc định
    }

    // API quay vòng quay may mắn
    public function spin(Request $request)
    {
        $user = $request->user();

        // Kiểm tra giới hạn 5 lượt quay/ngày
        // $todaySpins = SpinHistory::where('user_id', $user->id)
        //     ->whereDate('spun_at', Carbon::today())
        //     ->count();

        // if ($todaySpins >= 5) {
        //     return response()->json(['message' => 'Đã đạt giới hạn 5 lượt quay/ngày'], 403);
        // }

        // Kiểm tra lượt quay còn lại
        $availableSpins = Spin::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->sum('spin_count');

        if ($availableSpins <= 0) {
            return response()->json(['message' => 'Bạn không còn lượt quay nào'], 403);
        }

        // Trừ lượt quay
        $spin = Spin::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        $spin->spin_count -= 1;
        if ($spin->spin_count <= 0) {
            $spin->delete();
        } else {
            $spin->save();
        }

        // Quay và nhận phần thưởng
        $reward = $this->getRandomReward();

        // Nếu trúng quà hiện vật, giảm số lượng tồn kho
        if ($reward['type'] === 'gift') {
            $gift = Gift::find($reward['id']);
            if ($gift && $gift->stock > 0) {
                $gift->stock -= 1;
                $gift->save();
            } else {
                // Nếu hết hàng, trả về "Chúc bạn may mắn lần sau"
                $reward = [
                    'type' => 'no_reward',
                    'id' => null,
                    'name' => 'Chúc bạn may mắn lần sau',
                ];
            }
        }

        // Lưu lịch sử quay
        SpinHistory::create([
            'user_id' => $user->id,
            'reward_type' => $reward['type'],
            'reward_id' => $reward['id'],
            'reward_name' => $reward['name'],
            'spun_at' => now(),
        ]);

        // Nếu trúng thêm lượt quay
        if ($reward['type'] === 'spin') {
            Spin::create([
                'user_id' => $user->id,
                'spin_count' => 1,
                'received_at' => now(),
                'expires_at' => now()->addDays(7),
            ]);
        }

        $response = ['reward' => $reward['name']];
            // Nếu trúng mã giảm giá ngẫu nhiên
            if ($reward['type'] === 'coupon' && strpos($reward['name'], 'Mã giảm giá ngẫu nhiên') !== false) {
                // Quyết định ngẫu nhiên loại mã: fixed hoặc percentage
                $discountType = rand(0, 1) ? 'fixed' : 'percentage';
        
                $fixedValues = [10000, 20000, 50000];
                $percentValues = [10, 20, 30];
                $discountValue = $discountType === 'fixed' 
                    ? $fixedValues[array_rand($fixedValues)] 
                    : $percentValues[array_rand($percentValues)];
        
                $steps = [10000, 20000, 30000, 40000, 50000];
                $discountMaxValue = $discountType === 'percentage' ? $steps[array_rand($steps)] : 0.00;
        
                // Tạo mã coupon ngẫu nhiên
                $couponCode = 'LUCKYWHEEL'. Str::upper(Str::random(6));
        
                // Kiểm tra mã trùng
                while (DB::table('coupons')->where('code', $couponCode)->exists()) {
                    $couponCode = 'LUCKYWHEEL'. Str::upper(Str::random(6));
                }
        
                // Tính ngày hết hạn (7 ngày từ hiện tại)
                $expireDate = now()->addDays(7);
        
                // Tạo tên coupon
                $couponName = $discountType === 'fixed' 
                    ? "Giảm " . number_format($discountValue) . " VNĐ" 
                    : "Giảm " . $discountValue . "% (Tối đa " . number_format($discountMaxValue) . " VNĐ)";
        
                try {
                    $admin = User::whereHas('roles', function ($query) {
                        $query->where('name', 'admin');
                    })->first();
                    
                    if (!$admin) {
                        throw new Exception('Không tìm thấy admin trong hệ thống.');
                    }
                    
                    $adminId = $admin->id;
                    // Lưu vào bảng coupons
                    $couponId = DB::table('coupons')->insertGetId([
                        'user_id' => $adminId,
                        'code' => $couponCode,
                        'name' => $couponName,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'discount_max_value' => $discountMaxValue,
                        'start_date' => now(),
                        'expire_date' => $expireDate,
                        'description' => 'Mã giảm giá tự động tạo khi quay thưởng',
                        'max_usage' => 1, // Mỗi mã chỉ dùng được 1 lần
                        'used_count' => 0,
                        'status' => 1, // Active
                        'specific_course' => 0, // Không áp dụng cho khóa học cụ thể
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
        
                    // Lưu vào bảng coupon_uses
                    DB::table('coupon_uses')->insert([
                        'user_id' => $user->id,
                        'coupon_id' => $couponId,
                        'status' => 'unused',
                        'applied_at' => now(),
                        'expired_at' => $expireDate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
        
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Có lỗi xảy ra khi tạo mã giảm giá.'], 500);
                }
        
                // Cập nhật response với thông tin mã giảm giá
                $response['coupon_code'] = $couponCode;
                $response['type'] = $discountType;
                $response['value'] = $discountValue;
                $response['max_value'] = $discountMaxValue;
                $response['expired_at'] = $expireDate;
            }

        return response()->json($response);
    }

    // Lấy số lượt quay còn lại
    public function getSpins(Request $request)
    {
        $user = $request->user();
        $availableSpins = Spin::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->sum('spin_count');

        return response()->json(['Số lượt quay còn lại' => $availableSpins]);
    }

    // Cập nhật membership và tặng lượt quay
    public function updateMembership(Request $request)
    {
        $user = $request->user();
        $membership = $request->input('membership'); // basic, premium
        $duration = $request->input('duration'); // 6 hoặc 12 tháng

        $user->membership = $membership;
        $user->membership_expiry = now()->addMonths($duration);
        $user->save();

        // Tặng lượt quay dựa trên gói
        $spinCount = $membership === 'basic' ? 1 : 2;
        if ($duration >= 12) {
            $spinCount += 2;
        } elseif ($duration >= 6) {
            $spinCount += 1;
        }

        Spin::create([
            'user_id' => $user->id,
            'spin_count' => $spinCount,
            'received_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['message' => 'Membership updated, spins added']);
    }

    // Hoàn thành hồ sơ và tặng lượt quay
    public function completeProfile(Request $request)
    {
        $user = $request->user();

        if ($user->profile_completed) {
            return response()->json(['message' => 'Hồ sơ đã hoàn thành trước đó'], 403);
        }

        $user->profile_completed = true;
        $user->save();

        Spin::create([
            'user_id' => $user->id,
            'spin_count' => 1,
            'received_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['message' => 'Hồ sơ hoàn thành, tặng 1 lượt quay']);
    }

    // Hoàn thành khóa học và tặng lượt quay
    public function completeCourse(Request $request)
    {
        $user = $request->user();
        $user->courses_completed += 1;
        $user->save();

        Spin::create([
            'user_id' => $user->id,
            'spin_count' => 1,
            'received_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['message' => 'Khóa học hoàn thành, tặng 1 lượt quay']);
    }

    // Lấy lịch sử quay của người dùng
    public function getSpinHistory(Request $request)
    {
        $user = $request->user();
        $history = SpinHistory::where('user_id', $user->id)
            ->select('reward_type', 'reward_id', 'reward_name', 'spun_at')
            ->orderBy('spun_at', 'desc')
            ->get();

        return response()->json($history);
    }

    // Lấy danh sách phần thưởng có thể trúng
    public function getAvailableRewards(Request $request)
    {
        $rewards = $this->getAllRewards();
        return response()->json($rewards);
    }
}
