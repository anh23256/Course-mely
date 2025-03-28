<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UserStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $isOnline;
    public $conversationId;

    public function __construct($isOnline, $conversationId)
    {
        $this->isOnline = $isOnline;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('conversation.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'UserStatusChanged';
    }

    public function broadcastWith()
    {
        return [
            'is_online' => $this->isOnline,
        ];
    }
}
