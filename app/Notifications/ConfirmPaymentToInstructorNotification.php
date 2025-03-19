<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ConfirmPaymentToInstructorNotification extends Notification
{
    use Queueable;

    private $withdrawal;
    private $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($withdrawal, $message)
    {
        $this->withdrawal = $withdrawal;
        $this->message = $message;
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
        return config('app.fe_url') . '/instructor/with-draw-request';
    }

    private function notificationData()
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'status' => $this->withdrawal->status,
            'amount' => $this->withdrawal->amount,
            'message' => $this->message,
            'link' => $this->getUrl(),
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
        $channel = new PrivateChannel('notification.' . $this->withdrawal->wallet->user_id);
        return $channel;
    }

}
