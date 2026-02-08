<?php

namespace App\Repositories;

interface FriendshipRepositoryInterface
{
    public function sendFriendRequest($authId, $receiverId);
    public function checkFriendshipStatus($authId, $username);
    public function acceptFriendRequest($authId, $senderId);
    public function unfriend($authId, $friendId);
    public function getFriends($authId);
    public function getFriendsWithPagination($authId, $perPage = 15);
    public function getFriendRequests($authId);
    public function getSentFriendRequests($authId);
}