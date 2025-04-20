<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\NewFollowNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class FollowController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function follow(string $code)
    {
        try {
            $follower = Auth::user();

            if (!$follower) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $instructor = User::where('code', $code)
                ->where('status', '!=', 'blocked')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'instructor');
                })->first();

            if (!$instructor) {
                return $this->respondNotFound('Không tìm thấy giảng viên.');
            }

            if ($follower->code == $code) return $this->respondError('Không thể theo dõi chính mình');

            $isFollowing = Follow::where([
                'follower_id' => $follower->id,
                'instructor_id' => $instructor->id
            ])->first();

            if ($isFollowing) {
                $isFollowing->delete();

                return $this->respondOk('Huỷ theo dõi thành công');
            } else {
                Follow::create([
                    'follower_id' => $follower->id,
                    'instructor_id' => $instructor->id
                ]);

                $instructor->notify(new NewFollowNotification($follower));

                return $this->respondOk('Theo dõi thành công');
            }
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getFollowersCount(string $code)
    {
        try {
            $instructor = User::where('code', $code)
                ->where('status', '!=', 'blocked')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'instructor');
                })->first();

            if (!$instructor) {
                return $this->respondNotFound('Không tìm thấy giảng viên.');
            }

            $total_follower = Follow::where('instructor_id', $instructor->id)
                ->count();

            $listFollower = DB::table('follows')->select('users.name', 'users.avatar', 'users.status', 'users.code')
                ->join('users', 'users.id', '=', 'follows.follower_id')->limit(20)->get();

            return response()->json([
                'message' => "Danh sách người theo dõi giảng viên {$instructor->name}",
                'total_follower' => $total_follower,
                'list_follower' => $listFollower,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Lấy danh sách người theo dõi giảng viên không thành công');
        }
    }

    public function checkUserFollower(string $code)
    {
        try {
            $user = Auth::user();
            $checkFollow = false;

            if (!$user) {
                return response()->json(['followed' => $checkFollow]);
            }

            $instructor = User::where('code', $code)
                ->where('status', '!=', 'blocked')
                ->whereHas('roles', fn($query) => $query->where('name', 'instructor'))
                ->select('id')
                ->first();

            $checkFollow = $instructor
                ? Follow::where(['follower_id' => $user->id, 'instructor_id' => $instructor->id])->exists()
                : false;

            return response()->json(['followed' => $checkFollow]);
        } catch (\Exception $e) {
            $this->logError($e);
            
            return $this->respondServerError('');
        }
    }
}
