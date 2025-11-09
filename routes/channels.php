<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ConversationMember;
use App\Models\Call;
use App\Models\CallParticipant;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// routes/channels.php


// Channel cho cuộc trò chuyện
Broadcast::channel('conversation.{id}', function ($user, $id) {
    // Kiểm tra xem user có phải là thành viên của cuộc trò chuyện không
    return ConversationMember::where('conversation_id', $id)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('call.{id}', function ($user, $id) {
    $call = Call::find($id);
    if (!$call) return false;

    return $call->participants()->where('user_id', $user->id)->exists()
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});

Broadcast::channel('call.{id}', function ($user, $id) {
    return CallParticipant::where('call_id', $id)
        ->where('user_id', $user->id)
        ->exists();
});





