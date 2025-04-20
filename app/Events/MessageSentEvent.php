<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $conversation)
    {
        $this->message = $message;
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('conversation.' . $this->conversation->id);
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }

    public function broadcastWith()
    {
        $sender = $this->message->sender;
        $parent = $this->message->parent;

        $broadcastData = [
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'meta_data' => $this->message->meta_data,
            'sender' => [
                'id' => $sender->id,
                'name' => $sender->name,
                'avatar' => $sender->avatar ?? null,
            ],
            'sent_at' => $this->message->created_at->toDateTimeString(),
        ];

        if ($parent) {
            $broadcastData['parent'] = [
                'id' => $parent->id,
                'content' => $parent->content,
                'sender' => [
                    'id' => $parent->sender->id,
                    'name' => $parent->sender->name,
                    'avatar' => $parent->sender->avatar ?? null,
                ],
            ];
        }

        return $broadcastData;
    }

}
