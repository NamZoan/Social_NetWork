<?php

namespace App\Repositories;

interface ConversationRepositoryInterface
{
    public function getUserConversations($userId);

    public function getConversationById($conversationId);
}