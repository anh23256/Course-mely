<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseModificationNotificationForStudent extends Notification
{
    use Queueable;

    protected $course;
    protected $student;

    /**
     * Create a new notification instance.
     */
    public function __construct($course, $student)
    {
        $this->course = $course;
        $this->student = $student;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    private function notificationData()
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->name,
            'message' => 'Khoá học ' . $this->course->name . ' đang trong quá trình sửa đổi.'
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData();
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage(
            $this->notificationData()
        );
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('member.' . $this->student->id);
        return $channel;
    }
}
