<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseModificationResponseNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $course;
    protected $status;
    protected $note;

    /**
     * Create a new notification instance.
     */
    public function __construct($course, $status, $note = '')
    {
        $this->course = $course;
        $this->status = $status;
        $this->note = $note;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'status' => $this->status,
            'message' => $this->getNotificationMessage(),
            'note' => $this->note
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'status' => $this->status,
            'message' => $this->getNotificationMessage(),
            'note' => $this->note
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notification.' . $this->course->user_id);
    }

    private function getNotificationMessage()
    {
        return match ($this->status) {
            'modify_request_approved' => 'Yêu cầu sửa đổi khóa học đã được phê duyệt',
            'modify_request_rejected' => 'Yêu cầu sửa đổi khóa học đã bị từ chối',
            default => 'Có cập nhật về khóa học'
        };
    }
}
