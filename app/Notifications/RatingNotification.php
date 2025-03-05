<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class RatingNotification extends Notification
{
    use Queueable;

    private $course;
    private $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($course, $user)
    {
        $this->course = $course;
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
        return config('app.fe_url') . '/instructor/ratings/' . $this->course->slug;
    }

    private function notificationData()
    {
        return [
            'type' => 'rating_course',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'thumbnail' => $this->course->thumbnail,
            'avatar' => $this->user->avatar,
            'message' => 'Khoá học"' . $this->course->name . '" vừa nhận được một lượt đánh giá',
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

    public function broadcastOn()
    {
        $channel = new PrivateChannel('instructor.' . $this->course->user_id);
        return $channel;
    }
}
