<?php

namespace App\Repositories;

interface GroupRepositoryInterface
{
    public function getCreatedGroups($userId);
    public function getJoinedGroups($userId);
    public function createGroup(array $data, $coverPhotoFile = null);
    public function addMember($group, $userId, $role, $status = 'active');
    public function isMember($group, $userId);
    public function isPending($group, $userId);
    public function removeMember($group, $userId);
    public function find($groupId);
    public function getMembers($group);
    public function updateGroup($group, array $data, $coverPhotoFile = null);
    public function deleteGroup($group);
}