<?php

namespace App\Repositories;

interface ConversationRepositoryInterface
{
    public function getUserConversations($userId);

    public function getConversationById($conversationId);

    public function createConversation(array $data);

    public function addMember($conversationId, $userId);

    public function removeMember($conversationId, $userId);

    public function isMember($conversationId, $userId): bool;

    public function getMessages($conversationId);

    public function addMessage($conversationId, array $data);

    // Thêm mới:
    public function findOrCreateIndividualConversation($userId1, $userId2);

    public function getMembers($conversationId);

    public function deleteConversation($conversationId, $userId);

    public function updateConversation($conversationId, array $data);
}