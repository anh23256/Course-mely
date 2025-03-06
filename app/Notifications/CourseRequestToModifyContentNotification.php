<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseRequestToModifyContentNotification extends Notification
{
    use Queueable;

    protected $course;

    /**
     * Create a new notification instance.
     */
    public function __construct($course)
    {
        $this->course = $course;
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
        $approvableId = $this->course->approvables ? $this->course->approvables->id : null;
        return $approvableId ? route('admin.approvals.courses.show', $approvableId) : '#';
    }

    private function notificationData(): array
    {
        return [
            'type' => 'register_course',
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'course_slug' => $this->course->slug,
            'course_thumbnail' => $this->course->thumbnail,
            'message' => 'Khóa học "' . $this->course->name . '" đã gửi yêu cầu sửa đổi nội dung.',
            'url' => $this->getUrl(),
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
