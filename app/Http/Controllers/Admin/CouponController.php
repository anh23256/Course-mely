<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupons\StoreCouponRequest;
use App\Http\Requests\Admin\Coupons\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\User;
use App\Notifications\CouponCodeCreated;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    use LoggableTrait, FilterTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryCoupons = Coupon::query()->with('user');

        if ($request->has('query') && $request->input('query')) {
            $search = $request->input('query');
            $queryCoupons->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%");
        }

        $couponCounts = Coupon::query()
            ->selectRaw('
                COUNT(id) as total_coupons,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_coupons,
                SUM(CASE WHEN expire_date < NOW() THEN 1 ELSE 0 END) as expire_coupons,
                SUM(CASE WHEN used_count > 0 THEN 1 ELSE 0 END) as used_coupons
            ')
            ->first();
        if ($request->hasAny(['name', 'discount_type', 'used_count', 'code', 'status', 'start_date', 'expire_date'])) {
            $queryCoupons = $this->filter($request, $queryCoupons);
        }

        $queryCouponCounts = Coupon::query()
            ->selectRaw('
            COUNT(id) as total_coupons,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_coupons,
            SUM(CASE WHEN expire_date < NOW() THEN 1 ELSE 0 END) as expire_coupons,
            SUM(CASE WHEN used_count > 0 THEN 1 ELSE 0 END) as used_coupons
        ');

        // Lấy dữ liệu và phân trang
        $coupons = $queryCoupons->orderBy('id', 'desc')->paginate(10);

        if ($request->ajax()) {
            $html = view('coupons.table', compact('coupons'))->render();
            return response()->json(['html' => $html]);
        }

        return view('coupons.index', compact('coupons', 'couponCounts'));
    }

    public function create()
    {
        return view('coupons.create');
    }

    public function store(StoreCouponRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            // Gửi thông báo cho người dùng hoặc giảng viên
            $coupon = Coupon::create($data);

            $roleUser = ['member', 'instructor'];

            $users = User::whereHas('roles', function ($query) use ($roleUser) {
                $query->whereIn('name', $roleUser);
            })->get();
            // $user = User::where('email', 'ducmely@gmail.com')->first();
            // $user->notify(new CouponCodeCreated($coupon));
            foreach ($users as $user) {
                $user->notify(new CouponCodeCreated($coupon));
            }
            DB::commit();
            return redirect()->route('admin.coupons.index')->with('success', 'Thêm mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('coupons.show', compact('coupon'));
    }

    public function edit(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, string $id)
    {
        try {
            DB::beginTransaction();
            $coupon = Coupon::findOrFail($id);
            $coupon->update($request->validated());
            $user = User::where('email', 'ducmely@gmail.com')->first();
            $admin = User::where('email', 'quaixe121811@gmail.com')->first();
            $user->notify(new CouponCodeCreated($coupon,$admin));
            DB::commit();
            return redirect()->route('admin.coupons.edit', $coupon->id)->with('success', 'Cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            Coupon::findOrFail($id)->delete();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Xóa dữ liệu thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e);
            return back()->with('error', 'Lỗi khi xóa');
        }
    }

    public function listDeleted(Request $request)
    {
        $queryCoupons = Coupon::onlyTrashed();

        if ($request->has('query')) {
            $search = $request->input('query');
            $queryCoupons->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%");
        }

        $coupons = $queryCoupons->orderBy('id', 'desc')->paginate(10);

        if ($request->ajax()) {
            $html = view('coupons.table', compact('coupons'))->render();
            return response()->json(['html' => $html]);
        }

        return view('coupons.deleted', compact('coupons'));
    }
    private function filter($request, $query)
    {
        $filters = [
            'start_date' => ['queryWhere' => '>='],
            'expire_date' => ['queryWhere' => '<='],
            'user_id' => ['queryWhere' => 'LIKE'],
            'name' => ['queryWhere' => 'LIKE'],
            'code' => ['queryWhere' => 'LIKE'],
            'status' => ['queryWhere' => '='],
            'discount_type' => ['queryWhere' => '='],
            'used_count' => ['queryWhere' => '>='],
            'deleted_at' => ['attribute' => ['start_deleted' => '>=', 'end_deleted' => '<=',]],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
