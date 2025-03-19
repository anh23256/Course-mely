<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class JoiFreeCourseNotification extends Notification
{
    use Queueable;

    protected $course;
    protected $student;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $student, Course $course)
    {
        $this->student = $student;
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

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Học viên ' . $this->student->name . ' đã tham gia khóa học miễn phí: ' . $this->course->name,
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Học viên ' . $this->student->name . ' đã tham gia khóa học miễn phí: ' . $this->course->name,
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->course->user_id);
        Log::info('Broadcasting on channel: ' . $channel->name);
        return $channel;
    }
}
