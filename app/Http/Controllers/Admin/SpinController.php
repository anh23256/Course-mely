<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpinRequest;
use App\Models\Coupon;
use App\Models\Gift;
use App\Models\Spin;
use App\Models\SpinConfig;
use App\Models\SpinHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SpinController extends Controller
{
    public function index(Request $request)
    {
        // Cấu hình tỷ lệ trúng
        $spinConfigs = SpinConfig::all();
        // Kiểm tra số lượng phần thưởng theo loại (no_reward, coupon, spin)
        $requiredTypes = ['no_reward', 'coupon', 'spin'];
        $spinConfigTypes = $spinConfigs->pluck('type')->unique()->toArray();
        $missingTypes = array_diff($requiredTypes, $spinConfigTypes);
        $hasEnoughSpinConfigTypes = count($missingTypes) === 0;

        // Lấy danh sách quà hiện vật được chọn
        $selectedGifts = Gift::where('is_selected', 1)->get();
        $requiredGifts = 2; // Số lượng quà hiện vật yêu cầu
        $currentSelectedGiftsCount = $selectedGifts->count();
        $hasEnoughGifts = $currentSelectedGiftsCount === $requiredGifts;

        // Kiểm tra xem có đủ điều kiện để tiếp tục hay không
        $isConfigValid = $hasEnoughSpinConfigTypes && $hasEnoughGifts;
        $showConfigWarning = !$isConfigValid;
        $gifts = Gift::all()->where('is_selected', 1);
        $giftsAll = Gift::all();
        $totalProbability = $spinConfigs->sum('probability') + $gifts->sum('probability');
        // Lấy danh sách quà hiện vật và mã giảm giá chưa được chọn để hiển thị trong modal
        $availableGifts = Gift::where('is_selected', 0)->get();
        // Tổng số lượt quay theo ngày, tháng, năm
        $spinStatsDay = SpinHistory::select(DB::raw('DATE(spun_at) as date'), DB::raw('COUNT(*) as spins'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $spinStatsMonth = SpinHistory::select(
            DB::raw('YEAR(spun_at) as year'),
            DB::raw('MONTH(spun_at) as month'),
            DB::raw('COUNT(*) as spins')
        )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $spinStatsYear = SpinHistory::select(DB::raw('YEAR(spun_at) as year'), DB::raw('COUNT(*) as spins'))
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // Danh sách người dùng trúng quà hiện vật
        $giftWinners = SpinHistory::where('reward_type', 'gift')
            ->with('user') // Giả sử có quan hệ user trong SpinHistory
            ->orderBy('spun_at', 'desc')
            ->get();
        // Thống kê số lượng quà đã trúng theo TÊN phần thưởng
        // Lấy tất cả phần thưởng đã trúng
        $winners = SpinHistory::select('reward_name', 'reward_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('reward_name', 'reward_type')
            ->get();

        // Gộp dữ liệu theo nhóm
        $groupedWinners = [
            'Mã giảm giá' => 0,
            'Quà hiện vật' => 0,
        ];

        foreach ($winners as $winner) {
            $rewardName = $winner->reward_name;
            $rewardType = $winner->reward_type;
            $count = $winner->total;

            // Phân loại mã giảm giá
            if ($rewardType === 'coupon' || stripos($rewardName, 'Mã giảm giá') !== false) {
                $groupedWinners['Mã giảm giá'] += $count;
            }
            // Phân loại quà hiện vật
            elseif ($rewardType === 'gift') {
                $groupedWinners['Quà hiện vật'] += $count;
            }
            // Các phần thưởng khác (giữ nguyên)
            else {
                $groupedWinners[$rewardName] = $count;
            }
        }

        $totalSpins = SpinHistory::count(); // Tổng số lượt quay
        $totalWinners = SpinHistory::whereNotIn('reward_type', ['no_reward', 'spin'])->count();
        $winRate = $totalSpins > 0 ? ($totalWinners / $totalSpins) * 100 : 0;
        return view('spins.index', compact(
            'spinConfigs',
            'gifts',
            'giftsAll',
            'availableGifts',
            'totalProbability',
            'spinStatsDay',
            'spinStatsMonth',
            'spinStatsYear',
            'giftWinners',
            'groupedWinners',
            'totalSpins',
            'totalWinners',
            'winRate',
            'spinConfigs',
            'selectedGifts',
            'hasEnoughSpinConfigTypes',
            'missingTypes',
            'hasEnoughGifts',
            'requiredGifts',
            'currentSelectedGiftsCount',
            'showConfigWarning'
        ));
    }
    public function storeSpinConfig(StoreSpinRequest $request)
    {
        $request->validated();

        $spinConfig = SpinConfig::create([
            'type' => $request->type,
            'name' => $request->name,
            'probability' => $request->probability,
        ]);

        return redirect()->back()->with('success', 'Thêm ô quà thành công!');
    }
    public function toggleSelection(Request $request, $type, $id)
    {
        if ($type === 'gift') {
            $item = Gift::findOrFail($id);
            if (!$item->is_selected) {
                // Đếm số lượng quà hiện vật đã được chọn
                $selectedGiftsCount = Gift::where('is_selected', 1)->count();

                // Nếu đã có 2 quà hiện vật được chọn, không cho phép thêm
                if ($selectedGiftsCount >= 2) {
                    return redirect()->back()->with('error', 'Chỉ được phép thêm tối đa 2 quà hiện vật vào vòng quay!');
                }
            }
        } else {
            return redirect()->back()->with('error', 'Loại phần thưởng không hợp lệ');
        }

        $item->is_selected = !$item->is_selected;
        $item->save();

        // Log::info("Admin toggled selection for $type", ['admin_id' => $request->user()->id, 'item_id' => $id, 'is_selected' => $item->is_selected]);
        $message = $item->is_selected
            ? 'Thêm quà hiện vật vào vòng quay thành công!'
            : 'Bỏ quà hiện vật khỏi vòng quay thành công!';
        return redirect()->back()->with('success', $message);
    }
    // Cập nhật cấu hình tỷ lệ trúng (SpinConfig)
    public function updateSpinConfig(Request $request, $id)
    {
        $config = SpinConfig::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'probability' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $config->probability = $request->probability;
        $config->save();

        Log::info('Admin updated spin config', ['admin_id' => $request->user()->id, 'config' => $config]);
        return redirect()->back()->with('success', 'Cập nhật tỷ lệ thành công');
    }

    // Thêm quà hiện vật
    public function addGift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'probability' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $gift = Gift::create($request->only(['name', 'stock', 'probability', 'description', 'image_url']));
        Log::info('Admin added gift', ['admin_id' => $request->user()->id, 'gift' => $gift]);
        return redirect()->back()->with('success', 'Thêm quà thành công');
    }

    // Cập nhật quà hiện vật
    public function updateGift(Request $request, $id)
    {
        $gift = Gift::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'probability' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $gift->update($request->only(['name', 'stock', 'probability', 'description', 'image_url', 'is_active']));
        Log::info('Admin updated gift', ['admin_id' => $request->user()->id, 'gift' => $gift]);
        return redirect()->back()->with('success', 'Cập nhật quà thành công');
    }

    // Xóa quà hiện vật
    public function deleteGift($id)
    {
        $gift = Gift::findOrFail($id);
        $gift->delete();
        Log::info('Admin deleted gift', ['admin_id' => request()->user()->id, 'gift_id' => $id]);
        return redirect()->back()->with('success', 'Xóa quà thành công');
    }
    public function deleteSpinConfig($id)
    {
        $spinConfig = SpinConfig::findOrFail($id);
        $spinConfig->delete();

        return response()->json(['success' => true, 'message' => 'Xóa ô quà thành công!']);
    }
}
