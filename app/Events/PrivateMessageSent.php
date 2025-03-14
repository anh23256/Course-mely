<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    // Xác định kênh mà sự kiện sẽ được phát đi (Chỉ phát tới những người trong cuộc trò chuyện)
    public function broadcastOn()
    {
        return new PrivateChannel('private-chat.' . $this->message->conversation_id);
    }

    // Định nghĩa tên sự kiện để client bắt
    // public function broadcastAs()
    // {
    //     return 'private-message.sent';
    // }

    // // Dữ liệu gửi đi cho frontend
    // public function broadcastWith()
    // {
    //     return [
    //         'id' => $this->message->id,
    //         'conversation_id' => $this->message->conversation_id,
    //         'sender_id' => $this->message->sender_id,
    //         'content' => $this->message->content,
    //         'created_at' => $this->message->created_at->toDateTimeString(),
    //     ];
    // }
}
