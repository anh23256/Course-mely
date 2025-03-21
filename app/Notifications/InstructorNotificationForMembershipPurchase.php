<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructorNotificationForMembershipPurchase extends Notification implements ShouldQueue
{
    use Queueable;

    private $buyer;
    private $membership;
    private $transaction;

    public function __construct($buyer, $membership, $transaction)
    {
        $this->buyer = $buyer;
        $this->membership = $membership;
        $this->transaction = $transaction;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Gói membership đã được đăng ký!')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Gói membership "' . $this->membership->name . '" đã được đăng ký bởi học: ' . $this->buyer->name)
            ->line('Thời hạn: ' . $this->membership->duration_months . ' tháng')
            ->line('Số tiền: ' . number_format($this->transaction->amount, 0, ',', '.') . ' VND')
            ->action('Xem chi tiết', 'ahhihi')
            ->line('Người dùng mới đã tham gia cộng đồng CourseMeLy!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Học viên ' . $this->buyer->name . ' đã đăng ký gói membership ',
            'user_id' => $this->buyer->id,
            'membership_name' => $this->membership->name,
            'membership_duration' => $this->membership->duration,
            'transaction_amount' => $this->transaction->amount,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Học viên ' . $this->buyer->name . ' đã đăng ký gói membership ',
            'user_id' => $this->buyer->id,
            'membership_name' => $this->membership->name,
            'membership_duration' => $this->membership->duration,
            'transaction_amount' => $this->transaction->amount,
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->membership->instructor_id);
        return $channel;
    }
}
