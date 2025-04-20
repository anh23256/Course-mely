<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CourseRejectedNotification extends Notification implements ShouldBroadcast, ShouldQueue
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'course_slug' => $this->course->slug,
            'course_thumbnail' => $this->course->thumbnail,
            'message' => 'Khóa học "' . $this->course->name . '" bị từ chối do không đủ điều kiện!',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'course_slug' => $this->course->slug,
            'course_thumbnail' => $this->course->thumbnail,
            'message' => 'Khóa học "' . $this->course->name . '" bị từ chối do không đủ điều kiện!',
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->course->user_id);
        Log::info('Broadcasting on channel: ' . $channel->name);
        return $channel;
    }
}

