<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GroupMessageSent extends MessageSentEvent
{

    public function broadcastOn()
    {
        return new PresenceChannel('conversation.'. $this->message->conversation_id);
    }
    public function broadcastAs()
    {
        return 'GroupMessageSent';
    }
}
