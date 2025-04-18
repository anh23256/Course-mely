<?php

namespace App\Notifications\Client;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InstructorModificationRate extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable, Notifiable;

    public $newRate;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $newRate, User $user)
    {
        $this->newRate = $newRate;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        Log::info($this->newRate);
        return (new MailMessage)
            ->view('emails.update_rate_instructor', [
                'newSharePercentage' => $this->newRate,
                'user' => $this->user
            ])
            ->subject('Thay đổi tỉ lệ doanh thu với giảng viên');
    }
}
