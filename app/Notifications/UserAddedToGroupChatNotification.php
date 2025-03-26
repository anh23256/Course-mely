<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAddedToGroupChatNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $conversation;

    public function __construct($conversation)
    {
        $this->conversation = $conversation;
    }


    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    private function notificationData()
    {
        return [
            'type' => 'user_added_group',
            'message' => "Bạn đã được thêm vào nhóm {$this->conversation->name}",
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
        return  new PrivateChannel('notification.' . $this->conversation->user_id);
    }
}
