<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

// Channel cho conversation
Broadcast::channel('conversation.{id}', function ($user, $id) {
    return Conversation::where('id', $id)
        ->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('conversation_id', $id);
        })
        ->exists();
});

// Channel cho user
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
