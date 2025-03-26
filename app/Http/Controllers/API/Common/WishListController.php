<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\WishList\StoreWishListRequest;
use App\Models\Course;
use App\Models\WishList;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishListController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            $courses = Course::query()
                ->with([
                    'category',
                    'wishLists',
                    'user',
                    'chapters' => function ($query) {
                        $query->withCount('lessons');
                    },
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])
                ->whereHas('wishLists', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            return $this->respondOk('Danh sách khoá học yêu thích của người dùng:' . $user->name, $courses);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWishListRequest $request)
    {
        try {
            $user = Auth::user();

            $existingWishList = WishList::where('user_id', $user->id)
                ->where('course_id', $request->course_id)
                ->exists();

            if ($existingWishList) {
                return $this->respondError('Khóa học đã tồn tại trong danh sách yêu thích');
            }

            $wishList = WishList::query()->firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $request->course_id,
            ]);

            return $this->respondCreated('Đã thêm khóa học vào danh sách yêu thích', $wishList);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();

            $wishList = WishList::query()
                ->where('user_id', $user->id)
                ->where('course_id', $id)
                ->first();

            if (!$wishList) {
                return $this->respondNotFound('Không tìm thấy khóa học trong danh sách yêu thích');
            }

            $wishList->delete();

            return $this->respondOk('Đã xóa khóa học khỏi danh sách yêu thích');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
