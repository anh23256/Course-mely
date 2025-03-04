<?php

namespace App\Notifications\Client;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    private $message;
    private $conversation;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $conversation)
    {
        $this->message = $message;
        $this->conversation = $message->conversation;
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

    private function notificationData($notifiable)
    {
        $isGroupChat = $this->conversation->type == 'group';
        $sender = $this->message->sender;

        $data = [
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'sender' => [
                'id' => $sender->id,
                'name' => $sender->name,
                'avatar' => $sender->avatar ?? null,
            ],
            'sent_at' => $this->message->created_at->toDateTimeString(),
        ];

        if ($isGroupChat) {
            $data['conversation_type'] = 'group';
            $data['group_name'] = $this->conversation->name;
        } else {
            $data['conversation_type'] = 'direct';
        }

        return $data;
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData($notifiable);
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->notificationData($notifiable));
    }
}
