<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $post;
    public $user;

    public function __construct($comment, $post, $user)
    {
        $this->comment = $comment;
        $this->post = $post;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->post->user_id);
    }

    public function broadcastAs()
    {
        return 'comment.added';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->comment->id,
            'type' => 'comment',
            'content' => $this->comment->content,
            'post_id' => $this->post->id,
            'post_content' => $this->post->content,
            'sender_id' => $this->user->id,
            'sender_name' => $this->user->name,
            'sender_avatar' => $this->user->avatar,
            'created_at' => now(),
            'action_url' => "/posts/{$this->post->id}"
        ];
    }
} 