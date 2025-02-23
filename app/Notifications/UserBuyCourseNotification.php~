<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class UserBuyCourseNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $user;
    public $course;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Course $course)
    {
        $this->user = $user;
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
        $transactionCode = $this->course->invoices->first()->transaction->transaction_code ?
            $this->course->invoices->first()->transaction->transaction_code :
            null;
        return $transactionCode ? route('admin.transactions.show', $transactionCode) : '#';
    }

    private function notificationData(): array
    {
        return [
            'type' => 'user_buy_course',
            'message' => $this->user->name . ' đã mua khóa học ' . $this->course->name,
            'user_avatar' => $this->user->avatar,
            'url' => 'Xem chi tiết ' . $this->getUrl()
        ];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage($this->notificationData());
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
