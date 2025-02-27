<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $countNotifications = $request->query('count_notifications', []);

            $typeGroups = [
                'approval' => [
                    'type' => ['register_course', 'register_instructor', "withdrawal"],
                    'count' => 10
                ],
                'message' => [
                    'type' => ['user_buy_course'],
                    'count' => 10
                ],
                'buycourse' => [
                    'type' => ['receive_message'],
                    'count' => 10
                ]
            ];

            if ($request->ajax() && $request->has('count_notifications') && !empty($countNotifications)) {
                foreach ($countNotifications as $key => $value) {
                    if (isset($typeGroups[$key])) {
                        $typeGroups[$key]['count'] = (int)$value['count'];
                    }
                }
            }

            $notifications = collect();

            foreach ($typeGroups as $key => $group) {
                $groupedNotifications = $user->notifications()
                    ->where(function ($query) use ($group) {
                        foreach ($group['type'] as $type) {
                            $query->orWhereJsonContains('data->type', $type);
                        }
                    })
                    ->latest()
                    ->take($group['count'] ?? 10)
                    ->get();
                $notifications = $notifications->merge($groupedNotifications);
            }

            $unreadNotificationsCount = $user->unreadNotifications()->count();

            return $this->respondOk('Danh sách thông báo', [
                'notifications' => $notifications,
                'unread_notifications_count' => $unreadNotificationsCount
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getUnreadNotificationsCount()
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $unreadNotificationsCount = $user->unreadNotifications()->count();

            return $this->respondOk('Số thông báo chưa đọc', [
                'unread_notifications_count' => $unreadNotificationsCount,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function markAsRead(string $notificationId, Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $notification = $user->notifications()->where('id', $notificationId)->first();

            if ($notification) {
                if (!$notification->read_at) {
                    $notification->markAsRead();
                }

                return $this->respondOk(
                    $notification->read_at ? 'Đánh dấu đã đọc thành công' : 'Đánh dấu chưa đọc thành công',
                );
            }

            return $this->respondError('Thông báo không tìm thấy');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
