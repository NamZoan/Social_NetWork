<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'conversation_type', 
        'creator_id', 
        'is_active',
        'image',
    ];

    // Mối quan hệ: Một cuộc trò chuyện có nhiều tin nhắn
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Mối quan hệ: Một cuộc trò chuyện thuộc về một người dùng (người tạo)
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Mối quan hệ: Một cuộc trò chuyện có nhiều thành viên
    public function members()
    {
        return $this->belongsToMany(User::class, 'conversation_members', 'conversation_id', 'user_id')
            ->withPivot('role', 'joined_at');
    }

    // Scope giúp tìm kiếm cuộc trò chuyện theo trạng thái hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Lấy cuộc trò chuyện với tin nhắn mới nhất
    public function scopeWithLatestMessage($query)
    {
        return $query->with(['messages' => function ($query) {
            $query->latest()->limit(1);
        }]);
    }

    // Lấy các cuộc trò chuyện của user với tin nhắn mới nhất
    public static function getConversationsWithLatestMessages($userId)
    {
        return self::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['members', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->orderByDesc(function ($query) {
            $query->select('sent_at')
                ->from('messages')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->limit(1);
        })
        ->get();
    }

    // Lấy tin nhắn mới nhất của cuộc trò chuyện
    public function getLatestMessage()
    {
        return $this->messages()
            ->with('sender')
            ->latest()
            ->first();
    }
}

