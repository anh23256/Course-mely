<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Gift;
use App\Models\Spin;
use App\Models\SpinHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SpinController extends Controller
{
    // Lấy danh sách phần thưởng từ coupons, gifts và thêm phần thưởng mặc định
    private function getAllRewards()
    {
        $rewards = [];

        // Phần thưởng mặc định 1: Chúc bạn may mắn lần sau
        $rewards[] = [
            'type' => 'no_reward',
            'id' => null,
            'name' => 'Chúc bạn may mắn lần sau',
            'probability' => 31,
        ];

        // Phần thưởng mặc định 2: Tặng thêm 1 lượt quay
        $rewards[] = [
            'type' => 'spin',
            'id' => null,
            'name' => 'Tặng thêm 1 lượt quay',
            'probability' => 20,
        ];

        // Lấy từ bảng coupons
        $coupons = Coupon::limit(4)->get();
        foreach ($coupons as $coupon) {
            $rewards[] = [
                'type' => 'coupon',
                'id' => $coupon->id,
                'name' => $coupon->name,
                'probability' => $coupon->probability,
            ];
        }

        // Lấy từ bảng gifts (chỉ lấy nếu còn hàng)
        $gifts = Gift::where('stock', '>', 0)->limit(2)->get();
        foreach ($gifts as $gift) {
            $rewards[] = [
                'type' => 'gift',
                'id' => $gift->id,
                'name' => $gift->name,
                'probability' => $gift->probability,
            ];
        }
        // Tính tổng tỷ lệ hiện tại
        $totalProbability = array_sum(array_column($rewards, 'probability'));

        // Nếu tổng tỷ lệ không đạt 100%, phân bổ lại
        if ($totalProbability < 100) {
            $remainingProbability = 100 - $totalProbability;
            $nonGiftRewards = array_filter($rewards, function ($reward) {
                return $reward['type'] !== 'gift'; // Chỉ lấy các phần thưởng không phải quà hiện vật
            });

            if (!empty($nonGiftRewards)) {
                $nonGiftCount = count($nonGiftRewards);
                $additionalProbability = $remainingProbability / $nonGiftCount;

                // Phân bổ lại tỷ lệ cho các phần thưởng không phải quà hiện vật
                foreach ($rewards as &$reward) {
                    if ($reward['type'] !== 'gift') {
                        $reward['probability'] += $additionalProbability;
                        // Làm tròn thành số nguyên
                        $reward['probability'] = round($reward['probability']);
                    }
                }
            }
        }

        // Kiểm tra lại tổng tỷ lệ và điều chỉnh nếu cần
        $totalProbability = array_sum(array_column($rewards, 'probability'));
        if ($totalProbability > 100) {
            // Giảm tỷ lệ của "Chúc bạn may mắn lần sau" để bù lại
            $excess = $totalProbability - 100;
            foreach ($rewards as &$reward) {
                if ($reward['name'] === 'Chúc bạn may mắn lần sau') {
                    $reward['probability'] -= $excess;
                    break;
                }
            }
        } elseif ($totalProbability < 100) {
            // Tăng tỷ lệ của "Chúc bạn may mắn lần sau" để bù lại
            $shortfall = 100 - $totalProbability;
            foreach ($rewards as &$reward) {
                if ($reward['name'] === 'Chúc bạn may mắn lần sau') {
                    $reward['probability'] += $shortfall;
                    break;
                }
            }
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

        // Nếu trúng coupon, trả về mã code
        $response = ['reward' => $reward['name']];
        if ($reward['type'] === 'coupon') {
            $coupon = Coupon::find($reward['id']);
            $response['coupon_code'] = $coupon->code;
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
