<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    use LoggableTrait, ApiResponseTrait, FilterTrait;

    public function index(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $countNotifications = $request->query('count_notifications', []);

            $typeGroups = [
                'approval' => [
                    'type' => ['register_course', 'register_instructor', "withdrawal", 'post_submitted'],
                    'count' => 10
                ],
                'message' => [
                    'type' => ['receive_message'],
                    'count' => 10
                ],
                'buycourse' => [
                    'type' => ['user_buy_course'],
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
                if (!empty($group['type'])) {
                    $groupedNotifications = $user->notifications()
                        ->whereIn('data->type', $group['type'])
                        ->latest()
                        ->take($group['count'] ?? 10)
                        ->get();

                    $notifications = $notifications->merge($groupedNotifications);
                }
            }

            $unreadNotificationsCount = $user->unreadNotifications()->count();

            return $this->respondOk('Danh sách thông báo', [
                'notifications' => $notifications,
                'unread_notifications_count' => $unreadNotificationsCount,
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


    public function allNotification(Request $request)
    {
        try {
            $title = 'Quản lý thông báo';
            $subTitle = 'Danh sách thông báo';

            /** @var User $user */
            $user = Auth::user();

            $notification_key = $request->query('notification_key', 'approval');

            $typeGroups = [
                'approval' => [
                    'type' => ['register_course', 'register_instructor', "withdrawal", 'post_submitted']
                ],
                'message' => [
                    'type' => ['receive_message']
                ],
                'buycourse' => [
                    'type' => ['user_buy_course']
                ]
            ];

            $queryNotifications = $user->notifications()
                ->where(function ($query) use ($notification_key, $typeGroups) {
                    if (!isset($typeGroups[$notification_key]['type']) || !is_array($typeGroups[$notification_key]['type'])) {
                        return;
                    }

                    $query->whereIn('data->type', $typeGroups[$notification_key]['type']);
                });

            // dd($request->all()); 

            $status = $request->input('status', 'all');
            if ($status === 'unread') {
                $queryNotifications->whereNull('read_at');
            } elseif ($status === 'read') {
                $queryNotifications->whereNotNull('read_at');
            }
            
            if ($request->has('search_full')) {

                $queryNotifications = $this->searchNotifications($request->search_full, $queryNotifications);
            }

            if ($request->has('notification_type')) {
                $queryNotifications = $this->filterType($request, $queryNotifications);
            }

            if ($request->hasAny(['created_at', 'updated_at'])) {
                $queryNotifications = $this->filter($request, $queryNotifications);
            }

            $notifications = $queryNotifications->with('notifiable')->latest()->paginate(10);

            if ($request->ajax()) {

                $html = view('notifications.table', compact('notifications'))->render();
                return response()->json(['html' => $html]);
            }

            return view('notifications.index', compact('notifications', 'title', 'subTitle'));
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function searchNotifications($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            return $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw("json_unquote(json_extract(data, '$.message')) LIKE ?", ["%{$searchTerm}%"]);
            });
        }

        return $query; // Trả về query gốc nếu không có tìm kiếm
    }

    private function filterType(Request $request, $query)
    {
        if ($request->has('notification_type')) {
            $type = $request->input('notification_type');

            $query->whereRaw("json_unquote(json_extract(data, '$.type')) LIKE ?", ["%{$type}%"]);
        }

        return $query;
    }

    private function filter($request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],

        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }


    public function forceDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $notificationID = explode(',', $id);

                $this->deleteNotifications($notificationID);
            } else {
                $notification = DatabaseNotification::query()->findOrFail($id);

                $notification->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }

    private function deleteNotifications(array $notificationID)
    {
        DatabaseNotification::query()->whereIn('id', $notificationID)->delete();
    }
}
