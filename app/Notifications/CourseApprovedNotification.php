<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CourseApprovedNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    protected $course;

    /**
     * Create a new notification instance.
     */
    public function __construct(Course $course)
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


    public function toDatabase($notifiable)
    {
        Log::info("Saving notification to the database for user ID: " . $notifiable->id . " | Course ID: " . $this->course->id);

        return [
            'type' => 'register_course',
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'course_slug' => $this->course->slug,
            'course_thumbnail' => $this->course->thumbnail,
            'message' => 'Khóa học "' . $this->course->name . '" đã được gửi yêu cầu kiểm duyệt.',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'course_slug' => $this->course->slug,
            'course_thumbnail' => $this->course->thumbnail,
            'message' => 'Khóa học của bạn đã được phê duyệt!',
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->course->user_id);
        Log::info('Broadcasting on channel: ' . $channel->name);
        return $channel;
    }
}
