<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CouponCodeCreated extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    protected $coupon;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($coupon,$user)
    {
        $this->coupon = $coupon;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    private function getUrl()
    {
        $url = config('app.fe_url');
        return $url . '/my-courses?tab=coupon';
    }

    public function notificationData()
    {
        return [
            'message' => 'Bạn vừa nhận được một mã từ nhân viên' . $this->coupon->user_id. ' Nhấn để nhận mã',
            'name' =>$this->coupon->user_id,
            'url' => $this->getUrl(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
