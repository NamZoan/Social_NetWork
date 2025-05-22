<?php

namespace App\Repositories;

use App\Models\Friendship;
use App\Models\User;

class FriendshipRepository implements FriendshipRepositoryInterface
{
    public function sendFriendRequest($authId, $receiverId)
    {
        $existingRequest = Friendship::where(function ($query) use ($authId, $receiverId) {
            $query->where('user_id_1', $authId)
                ->where('user_id_2', $receiverId);
        })->orWhere(function ($query) use ($authId, $receiverId) {
            $query->where('user_id_1', $receiverId)
                ->where('user_id_2', $authId);
        })->first();

        if ($existingRequest) {
            return false;
        }

        Friendship::create([
            'user_id_1' => $authId,
            'user_id_2' => $receiverId,
            'status' => 'pending',
        ]);
        return true;
    }

    public function checkFriendshipStatus($authId, $username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return 'not_found';
        }

        $friendship = Friendship::where(function ($query) use ($authId, $user) {
            $query->where('user_id_1', $authId)
                ->where('user_id_2', $user->id);
        })->orWhere(function ($query) use ($authId, $user) {
            $query->where('user_id_1', $user->id)
                ->where('user_id_2', $authId);
        })->first();

        if (!$friendship) {
            return 'none';
        }

        if ($friendship->user_id_1 === $authId && $friendship->status === 'pending') {
            return 'sent';
        }

        if ($friendship->user_id_2 === $authId && $friendship->status === 'pending') {
            return 'received';
        }

        if ($friendship->status === 'accepted') {
            return 'friends';
        }

        return 'none';
    }

    public function acceptFriendRequest($authId, $senderId)
    {
        $friendship = Friendship::where('user_id_1', $senderId)
            ->where('user_id_2', $authId)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return false;
        }

        $friendship->update(['status' => 'accepted']);
        return true;
    }

    public function unfriend($authId, $friendId)
    {
        $friendship = Friendship::where(function ($query) use ($authId, $friendId) {
            $query->where('user_id_1', $authId)
                ->where('user_id_2', $friendId);
        })->orWhere(function ($query) use ($authId, $friendId) {
            $query->where('user_id_1', $friendId)
                ->where('user_id_2', $authId);
        })->first();

        if (!$friendship) {
            return false;
        }

        $friendship->delete();
        return true;
    }

    public function getFriends($authId)
    {
        $friendships = Friendship::where(function ($query) use ($authId) {
            $query->where('user_id_1', $authId)
                ->orWhere('user_id_2', $authId);
        })->where('status', 'accepted')->get();

        $friendIds = $friendships->map(function ($friendship) use ($authId) {
            return $friendship->user_id_1 == $authId
                ? $friendship->user_id_2
                : $friendship->user_id_1;
        })->toArray();

        return User::whereIn('id', $friendIds)->select('id', 'name', 'username', 'avatar')->get();
    }

    public function getFriendRequests($authId)
    {
        $requests = Friendship::where('user_id_2', $authId)
            ->where('status', 'pending')
            ->with('sender:id,name,username,avatar')
            ->get();

        return $requests->map(function ($request) {
            return $request->sender;
        });
    }
}