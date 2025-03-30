<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpinReceivedNotification extends Notification
{
    use Queueable;

    protected $userId;
    protected $spinCount;
    protected $expiresAt;

    public function __construct($userId, $spinCount, $expiresAt)
    {
        $this->userId = $userId;
        $this->spinCount = $spinCount;
        $this->expiresAt = $expiresAt;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    private function notificationData()
    {
        return [
            'type' => 'spin_received',
            'message' => "Bạn đã nhận được {$this->spinCount} lượt quay miễn phí! Hết hạn vào: {$this->expiresAt->format('d/m/Y H:i')}.",
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notification.' . $this->userId);
    }
}
