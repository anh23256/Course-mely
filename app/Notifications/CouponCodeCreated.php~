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
    /**
     * Create a new notification instance.
     */
    public function __construct($coupon)
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
        return  'Chưa tao duong dan';
    }

    public function notificationData()
    {
        return [
            'message' => 'Mã giảm giá "' . $this->coupon->name . '" đã được tạo. Nhấn để nhận mã.',
            'id' => $this->coupon->id,
            'code' => $this->coupon->code,
            'name' => $this->coupon->name,
            'discount_value' => $this->coupon->discount_value,
            'action_url' => $this->getUrl(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
