<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $withdrawalRequest;

    public $instructor;

    /**
     * Create a new notification instance.
     */
    public function __construct($withdrawalRequest, $instructor)
    {
        $this->withdrawalRequest = $withdrawalRequest;
        $this->instructor = $instructor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Yêu cầu rút tiền mới')
            ->line('Giảng viên: ' . $this->instructor->name . ' đã gửi yêu cầu rút tiền.')
            ->line('Số tiền yêu cầu: ' . number_format($this->withdrawalRequest->amount) . ' VND')
            ->line('Ngân hàng: ' . $this->withdrawalRequest->bank_name)
            ->line('Ngày yêu cầu: ' . $this->withdrawalRequest->request_date)
            ->action('Xem chi tiết', url('/admin/withdrawals/' . $this->withdrawalRequest->id))
            ->line('Cảm ơn bạn đã sử dụng hệ thống của chúng tôi!');
    }

    private function notificationData()
    {
        return [
            'type' => 'withdrawal',
            'withdrawal_id' => $this->withdrawalRequest->id,
            'amount' => $this->withdrawalRequest->amount,
            'bank_name' => $this->withdrawalRequest->bank_name,
            'request_date' => $this->withdrawalRequest->request_date,
            'instructor_name' => $this->instructor->name,
            'instructor_avatar' => $this->instructor->avatar,
            'message' => 'Giảng viên ' . $this->instructor->name . ' vừa gửi yêu cầu rút tiền.',
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
}
