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
    public function sendFriendRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $auth_id = auth()->id();
        $receiver_id = $request->user_id;

        // Kiểm tra nếu đã có lời mời kết bạn
        $existingRequest = Friendship::where(function ($query) use ($auth_id, $receiver_id) {
            $query->where('user_id_1', $auth_id)
                ->where('user_id_2', $receiver_id);
        })->orWhere(function ($query) use ($auth_id, $receiver_id) {
            $query->where('user_id_1', $receiver_id)
                ->where('user_id_2', $auth_id);
        })->first();

        if ($existingRequest) {
            return response()->json(['message' => 'Đã có lời mời kết bạn'], 400);
        }

        // Tạo lời mời kết bạn mới
        Friendship::create([
            'user_id_1' => $auth_id,
            'user_id_2' => $receiver_id,
            'status' => 'pending',
        ]);

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

        if (!$friendship) {
            return response()->json(['status' => 'none']);
        }

        // Nếu user đăng nhập là người gửi lời mời
        if ($friendship->user_id_1 === $auth_id && $friendship->status === 'pending') {
            return response()->json(['status' => 'sent']);
        }

        // Nếu user đăng nhập là người nhận lời mời
        if ($friendship->user_id_2 === $auth_id && $friendship->status === 'pending') {
            return response()->json(['status' => 'received']);
        }

        // Nếu đã là bạn bè
        if ($friendship->status === 'accepted') {
            return response()->json(['status' => 'friends']);
        }

        return response()->json(['status' => 'none']);
    }

    public function acceptFriendRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $auth_id = auth()->id();
        $sender_id = $request->user_id;

        $friendship = Friendship::where('user_id_1', $sender_id)
            ->where('user_id_2', $auth_id)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Không tìm thấy lời mời kết bạn'], 404);
        }

        $friendship->update(['status' => 'accepted']);

    }

    public function unfriend(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $auth_id = auth()->id();
        $friend_id = $request->user_id;

        $friendship = Friendship::where(function ($query) use ($auth_id, $friend_id) {
            $query->where('user_id_1', $auth_id)
                ->where('user_id_2', $friend_id);
        })->orWhere(function ($query) use ($auth_id, $friend_id) {
            $query->where('user_id_1', $friend_id)
                ->where('user_id_2', $auth_id);
        })->first();

        if (!$friendship) {
            return response()->json(['message' => 'Không tìm thấy bạn bè'], 404);
        }

        $friendship->delete();

    }
}
