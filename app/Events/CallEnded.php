<?php
namespace App\Events;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CallEnded implements ShouldBroadcast {
  public function __construct(public int $id, public int $byUserId) {}
  
  public function broadcastOn(){ 
    return new PresenceChannel("call.$this->id"); 
  }
  
  public function broadcastAs(){ 
    return 'call.ended'; 
  }
  
  public function broadcastWith(){
    return [
      'byUserId' => $this->byUserId,
    ];
  }
}