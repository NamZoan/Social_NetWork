<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Models\User;
use App\Models\Message;

class ConversationRepository implements ConversationRepositoryInterface
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

    /**
     * Tạo một cuộc trò chuyện mới.
     *
     * @param array $data
     * @return Conversation|null
     */
    public function createConversation(array $data)
    {
        return Conversation::create($data);
    }

    /**
     * Thêm thành viên vào cuộc trò chuyện.
     *
     * @param int $conversationId
     * @param int $userId
     * @return bool
     */
    public function addMember($conversationId, $userId)
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $conversation->members()->attach($userId);
            return true;
        }
        return false;
    }

    /**
     * Xóa thành viên khỏi cuộc trò chuyện.
     *
     * @param int $conversationId
     * @param int $userId
     * @return bool
     */
    public function removeMember($conversationId, $userId)
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $conversation->members()->detach($userId);
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra xem người dùng có phải là thành viên của cuộc trò chuyện không.
     *
     * @param int $conversationId
     * @param int $userId
     * @return bool
     */
    public function isMember($conversationId, $userId): bool
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            return $conversation->members()->where('user_id', $userId)->exists();
        }
        return false;
    }

    /**
     * Lấy danh sách tin nhắn của cuộc trò chuyện.
     *
     * @param int $conversationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMessages($conversationId)
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            return $conversation->messages()->with('sender')->orderBy('sent_at', 'asc')->get();
        }
        return collect();
    }

    /**
     * Thêm tin nhắn vào cuộc trò chuyện.
     *
     * @param int $conversationId
     * @param array $data
     * @return Message|null
     */
    public function addMessage($conversationId, array $data)
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            return $conversation->messages()->create($data);
        }
        return null;
    }

    /**
     * Tìm hoặc tạo cuộc trò chuyện cá nhân giữa 2 user.
     */
    public function findOrCreateIndividualConversation($userId1, $userId2)
    {
        $conversation = Conversation::where('conversation_type', 'individual')
            ->whereHas('members', function ($q) use ($userId1) {
                $q->where('user_id', $userId1);
            })
            ->whereHas('members', function ($q) use ($userId2) {
                $q->where('user_id', $userId2);
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Thêm creator_id khi tạo mới
        $conversation = Conversation::create([
            'conversation_type' => 'individual',
            'creator_id' => $userId1, // hoặc $userId2, tuỳ bạn chọn ai là người tạo
        ]);
        $conversation->members()->attach([$userId1, $userId2]);
        return $conversation;
    }

    /**
     * Lấy danh sách thành viên của cuộc trò chuyện.
     */
    public function getMembers($conversationId)
    {
        $conversation = Conversation::find($conversationId);
        return $conversation ? $conversation->members : collect();
    }

    /**
     * Xóa cuộc trò chuyện.
     */
    public function deleteConversation($conversationId, $userId)
    {
        // Xóa thành viên khỏi cuộc trò chuyện (chỉ với user hiện tại)
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $conversation->members()->detach($userId);
            return true;
        }
        return false;
    }

    /**
     * Cập nhật thông tin nhóm.
     */
    public function updateConversation($conversationId, array $data)
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $conversation->update($data);
            return $conversation;
        }
        return null;
    }
}