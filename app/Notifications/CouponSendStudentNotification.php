<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CouponSendStudentNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    private $instructor;

    private $user;

    /**
     * Create a new notification instance.
     */
    public function __construct( $instructor, $user)
    {
        $this->instructor = $instructor;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    private function getUrl()
    {
        $url = config('app.fe_url');
        return $url . '/my-courses?tab=coupon';
    }

    private function notificationData()
    {
        return [
            'type' => 'coupon_send_student',
            'message' => 'Bạn vừa nhận được một mã từ giảng viên' . $this->instructor->name,
            'url' => $this->getUrl()
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('member.' . $this->user->id);
        return $channel;
    }
}
