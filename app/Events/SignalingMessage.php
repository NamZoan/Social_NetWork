<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel; // Quan trọng
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Quan trọng
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignalingMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $callId,
        public array $payload,   // {from, to, type: 'offer|answer|candidate|hangup|toggle', data:{}}
    ) {}

    public function broadcastOn()
    {
        return new PresenceChannel("call.$this->callId");
    }

    public function broadcastAs()
    {
        return 'signal';
    }

    public function broadcastWith()
    {
        return [
            'payload' => $this->payload,
        ];
    }
}
