<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

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
        return route('admin.chats.index');
    }

    private function notificationData(object $notifiable, bool $isDatabase = false): array
    {
        $existingNotification = $notifiable->notifications()
            ->where('read_at', '==', null)
            ->whereJsonContains('data->conversation_id', $this->message->conversation_id)
            ->first();

        $count = $existingNotification ? (!empty($existingNotification->data['count']) ? $existingNotification->data['count'] + 1 : 1) : 1;

        return [
            'type' => 'receive_message',
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'conversation_id' => $this->message->conversation_id,
            'message_user_avatar' => $this->message->sender->avatar,
            'count' => $isDatabase ? $count : ($existingNotification->data['count'] ?? 1),
            'message' => 'Bạn có ' . ($isDatabase ? $count : ($existingNotification->data['count'] ?? 1)) . ' tin nhắn mới từ' .
                ($this->message->conversation->type == "group"
                    ? ' nhóm ' . $this->message->conversation->name
                    : ' người dùng ' . $this->message->sender->name),
            'url' => $this->getUrl(),
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $notificationData = $this->notificationData($notifiable, true);

        $existingNotification = $notifiable->notifications()
            ->where('read_at', '=', null)
            ->whereJsonContains('data->conversation_id', $this->message->conversation_id)
            ->first();

        if ($existingNotification) {
            $existingNotification->delete();
        }

        return $notificationData;
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData($notifiable, false));
    }
}
