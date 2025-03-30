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

class InstructorApprovalNotification extends Notification implements ShouldBroadcast, ShouldQueue
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
            ->subject('Phản hồi yêu cầu kiểm duyệt người hướng dẫn')
            ->view('emails.instructor-approval-response', [
                'user' => $this->user
            ]);
    }

    private function notificationData()
    {
        return [
            'type' => 'register_instructor',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'message' => 'Chúc mừng bạn đã trở thành giảng viên của CourseMeLy',
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
            'message' => 'Chúc mừng bạn đã trở thành giảng viên của CourseMeLy',
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->user->id);
        return $channel;
    }
}
