<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Coupons\StoreCouponRequest;
use App\Http\Requests\API\Coupons\UpdateCouponRequest;
use App\Models\Coupon;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use ApiResponseTrait;
    public function index()
    {
        //
        try {
            //code...
            $user = Auth::user();

            $coupons = Coupon::where('user_id',$user->id)->get();

            if($coupons->isEmpty())
            {
                return $this->respondForbidden('Không có mã giảm giá nào!');
            }

            return $this->respondOk('Danh sách mã giảm giá' , $coupons);
            
        } catch (\Exception $e) {
            //throw $th;

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        //
        try {
            //code...

            $data = $request->validated();

            $data['user_id'] = Auth::id();

            $data['status'] = 1;

            $data['used_count'] = 0;

            if($data['user_id']  !== Auth::id())
            {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $coupon = Coupon::create($data);

            return $this->respondCreated('Tạo mã giảm giá thành công', $coupon);

        } catch (\Exception $e) {
            //throw $th;

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, string $id)
    {
        //
        try {
            //code...
            $coupon = Coupon::find($id);

            $data = $request->validated();

            if(!$coupon)
            {
                return $this->respondForbidden('Không có mã giảm giá nào!');
            }

            if($coupon->user_id  !== Auth::id())
            {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $coupon->update($data);

            return $this->respondOk('Thao tác thành công' , $coupon);
            
        } catch (\Exception $e) {
            //throw $th;

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            //code...
            $coupon = Coupon::findOrFail($id);

            if(!$coupon)
            {
                return $this->respondForbidden('Không có mã giảm giá nào!');
            }

            if($coupon->user_id  !== Auth::id())
            {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $coupon->delete();

            return $this->respondOk('Thao tác thành công' );
            
        } catch (\Exception $e) {
            //throw $th;

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
