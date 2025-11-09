<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;
use Carbon\Carbon;
class SendMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender;

    public $notification;

    public $conversation;

    public function __construct(Message $message, User $sender, Notification $notification, Conversation $conversation)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->notification = $notification;
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id)
        ];

        // Get all members of the conversation
        $conversation = Conversation::with('members')->find($this->message->conversation_id);
        if ($conversation) {
            foreach ($conversation->members as $member) {
                if ($member->id !== $this->sender->id) {
                    $channels[] = new PrivateChannel('user.' . $member->id);
                }
            }
        }



        return $channels;
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'content' => $this->message->content,
                'message_type' => $this->message->message_type,
                'attachment_url' => $this->message->attachment_url,
                'sender_id' => $this->sender->id,
                'sender' => [
                    'id' => $this->sender->id,
                    'name' => $this->sender->name,
                    'avatar' => $this->sender->avatar,
                ],
                'sent_at' => $this->message->sent_at ? $this->message->sent_at->toIso8601String() : null,
            ],
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type,
                'data' => [
                    'message' => $this->notification->message,
                    'action_url' => $this->notification->action_url
                ],
                'read_at' => $this->notification->read_at,
                'message' => $this->notification->message,
                'action_url' => $this->notification->action_url,
                'created_at' => Carbon::parse($this->notification->created_at)->toIso8601String(),
            ],
            'conversation' => [
                'id' => $this->conversation->id,
                'conversation_type' => $this->conversation->conversation_type,
                'name' => $this->conversation->name,
                'image' => $this->conversation->image,
            ],
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'avatar' => $this->sender->avatar,
            ]
            
            
        ];
    }
}
