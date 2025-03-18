<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FriendshipController extends Controller
{
    public function sendRequest(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $sender_id = Auth::id();
        $receiver_id = $request->receiver_id;

        // Kiểm tra nếu đã có yêu cầu kết bạn trước đó
        $exists = Friendship::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('user_id_1', $sender_id)->where('user_id_2', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('user_id_1', $receiver_id)->where('user_id_2', $sender_id);
        })->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Đã gửi yêu cầu hoặc đã là bạn bè.');
        }

        // Lưu vào database
        Friendship::create([
            'user_id_1' => $sender_id,
            'user_id_2' => $receiver_id,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Gửi lời mời kết bạn thành công.');
    }

    public function checkFriendshipStatus(Request $request, $username)
{
    $user = User::where('username', $username)->first();
    if (!$user) {
        return response()->json(['status' => 'not_found'], 404);
    }

    $auth_id = auth()->id();
    $friendship = Friendship::where(function ($query) use ($auth_id, $user) {
        $query->where('user_id_1', $auth_id)
              ->where('user_id_2', $user->id);
    })->orWhere(function ($query) use ($auth_id, $user) {
        $query->where('user_id_1', $user->id)
              ->where('user_id_2', $auth_id);
    })->first();

    return response()->json([
        'status' => $friendship ? $friendship->status : 'none',
    ]);
}
}
