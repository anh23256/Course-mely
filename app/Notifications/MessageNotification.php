<?php

namespace App\Notifications;

use App\Models\message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MessageNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
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
        return  route('admin.chats.index');
    }

    private function notificationData(): array
    {
        return [
            'type' => 'receive_message',
            'message_id' => $this->message->id,
            'sender_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'message_user_avatar' => $this->message->sender->avatar,
            'message' => 'Bạn có 1 tin nhắn mới từ' . ($this->message->conversation->type == "group" ? ' nhóm ' .$this->message->conversation->name :  ' người dùng '. $this->message->sender->name),
            'url' => $this->getUrl(),
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
