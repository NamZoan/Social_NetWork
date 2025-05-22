<?php

namespace App\Repositories;

interface ProfileRepositoryInterface
{
    public function getUserByUsername($username);
    public function getUserFriends($userId);
    public function getFriendsIds($userId);
    public function countMutualFriends($userId1, $userId2);
    public function getUserPosts($user, $currentUser, $page = 1);
}