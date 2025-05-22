<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\FriendshipRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FriendshipController extends Controller
{
    protected $friendshipRepo;

    public function __construct(FriendshipRepositoryInterface $friendshipRepo)
    {
        $this->friendshipRepo = $friendshipRepo;
    }

    public function sendFriendRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $auth_id = auth()->id();
        $receiver_id = $request->user_id;

        $result = $this->friendshipRepo->sendFriendRequest($auth_id, $receiver_id);

        if (!$result) {
            return response()->json(['message' => 'Đã có lời mời kết bạn'], 400);
        }
        return response()->json(['message' => 'Đã gửi lời mời kết bạn']);
    }

    public function checkFriendshipStatus($username)
    {
        $auth_id = auth()->id();
        $status = $this->friendshipRepo->checkFriendshipStatus($auth_id, $username);

        if ($status === 'not_found') {
            return response()->json(['status' => 'not_found'], 404);
        }
        return response()->json(['status' => $status]);
    }

    public function acceptFriendRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $auth_id = auth()->id();
        $sender_id = $request->user_id;

        $result = $this->friendshipRepo->acceptFriendRequest($auth_id, $sender_id);

        
    }

    public function unfriend(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $auth_id = auth()->id();
        $friend_id = $request->user_id;

        $result = $this->friendshipRepo->unfriend($auth_id, $friend_id);

        if (!$result) {
            return response()->json(['message' => 'Không tìm thấy bạn bè'], 404);
        }
        return response()->json(['message' => 'Đã hủy kết bạn']);
    }

    public function getFriends()
    {
        $auth_id = auth()->id();
        $friends = $this->friendshipRepo->getFriends($auth_id);
        return $friends;
    }

    public function index()
    {
        $auth_id = auth()->id();
        $senders = $this->friendshipRepo->getFriendRequests($auth_id);

        return Inertia::render('Friends/Request', [
            'requests' => $senders,
        ]);
    }
}
