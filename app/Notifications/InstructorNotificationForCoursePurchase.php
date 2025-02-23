<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InstructorNotificationForCoursePurchase extends Notification
{
    use Queueable;

    private $buyer;
    private $course;
    private $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct($buyer, $course, $transaction)
    {
        $this->buyer = $buyer;
        $this->course = $course;
        $this->transaction = $transaction;
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
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Khóa học của bạn đã được mua!')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Khóa học "' . $this->course->name . '" đã được mua bởi học viên: ' . $this->buyer->name)
            ->line('Số tiền: ' . number_format($this->transaction->amount, 0, ',', '.') . ' VND')
            ->action('Xem chi tiết', url('/instructor/courses/' . $this->course->id))
            ->line('Chúc mừng bạn đã có thêm một học viên!');
    }


    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Học viên ' . $this->buyer->name . 'đã mua khoá học ',
            'student_id' => $this->buyer->id,
            'course_name' => $this->course->name,
            'transaction_amount' => $this->transaction->amount,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Học viên ' . $this->buyer->name . 'đã mua khoá học ',
            'student_id' => $this->buyer->id,
            'course_name' => $this->course->name,
            'transaction_amount' => $this->transaction->amount,
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('instructor.' . $this->course->user_id);
        Log::info('Broadcasting on channel: ' . $channel->name);
        return $channel;
    }
}
