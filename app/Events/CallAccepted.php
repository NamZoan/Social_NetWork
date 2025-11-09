<?php
namespace App\Events;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CallAccepted implements ShouldBroadcast {
  public function __construct(public int $id, public int $byUserId) {}
  
  public function broadcastOn(){ 
    return new PresenceChannel("call.$this->id"); 
  }
  
  public function broadcastAs(){ 
    return 'call.accepted'; 
  }
  
  public function broadcastWith(){
    return [
      'byUserId' => $this->byUserId,
    ];
  }
}