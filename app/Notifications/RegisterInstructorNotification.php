<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterInstructorNotification extends Notification implements ShouldBroadcast, ShouldQueue
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
            ->view('emails.instructor-approval', [
                'user' => $this->user,
                'notifiable' => $notifiable,
                'url' => $this->getUrl()
            ]);
    }

    private function getUrl()
    {
        $approvableId = $this->user->approvables ? $this->user->approvables->id : null;
        return $approvableId ? route('admin.approvals.instructors.show', $approvableId) : '#';
    }

    private function notificationData()
    {
        return [
            'type' => 'register_instructor',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'message' => 'Giảng viên "' . $this->user->name . '" đã được gửi yêu cầu kiểm duyệt.',
            'url' => $this->getUrl(),
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
