<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function ($user, $id) {
    return \App\Models\Conversation::where('id', $id)
        ->whereHas('members', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->exists();
});
