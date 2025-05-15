<?php

namespace App\Repositories;

use App\Models\Conversation;

class ConversationRepository
{
    /**
     * Lấy danh sách các cuộc trò chuyện của người dùng.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserConversations($userId)
    {
        return Conversation::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('members', 'messages')->get();
    }

    /**
     * Lấy thông tin chi tiết của một cuộc trò chuyện.
     *
     * @param int $conversationId
     * @return Conversation|null
     */
    public function getConversationById($conversationId)
    {
        return Conversation::with('members', 'messages')->find($conversationId);
    }
}