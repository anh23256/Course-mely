<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructorRejectedNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Yêu cầu phê duyệt giảng viên')
            ->line('Rất xin lỗi. Hiện tại bạn không đủ điều kiện để trở thành giảng viên trên hệ thống của chúng tôi!!!')
            ->line('Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!');
    }

    private function notificationData()
    {
        return [
            'type' => 'register_instructor',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'message' => 'Rất xin lỗi. Hiện tại bạn không đủ điều kiện để trở thành giảng viên trên hệ thống của chúng tôi!!!',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'message' => 'Rất xin lỗi. Hiện tại bạn không đủ điều kiện để trở thành giảng viên trên hệ thống của chúng tôi!!!',
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->user->id);
        return $channel;
    }
}
