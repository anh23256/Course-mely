<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getNotifications(Request $request)
    {
        try {
            $user = Auth::user();

            $limit = $request->get('limit', 5);
            $page = $request->get('page', 1);

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            $hasNextPage = $notifications->hasMorePages();

            return $this->respondOk('Danh sách thông báo', [
                'notifications' => $notifications->items(),
                'next_page' => $hasNextPage ? (int)$page + 1 : null,
                'total' => $notifications->total(),
                'has_more' => $hasNextPage
            ]);
        } catch (\Exception $e) {
            $this->log($e, $request->all());

            return $this->respondInternalError();
        }
    }

    public function markAsRead(string $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $notification = $user->notifications()->find($id);

            if ($notification) {
                $notification->markAsRead();
            }

            return $this->respondOk('Đánh dấu đã đọc thành công');
        } catch (\Exception $e) {
            $this->log($e);
            return $this->respondInternalError();
        }
    }
}
