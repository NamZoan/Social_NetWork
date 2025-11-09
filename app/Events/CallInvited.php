<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CallInvited implements ShouldBroadcast
{
    public function __construct(
        public int $id,
        public int $calleeId,
        public array $caller
    ) {
    }

    public function broadcastOn()
    {
        return new PrivateChannel("user.{$this->calleeId}");
    }

    public function broadcastAs()
    {
        return 'call.invited';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->id,
            'callee_id' => $this->calleeId,
            'caller' => $this->caller,
        ];
    }
}
