<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false; // Tắt timestamps

    protected $fillable = [
        'sender_id',
        'conversation_id',
        'content',
        'sent_at',
        'message_type',
        'attachment_url',
        'is_deleted'
    ];

    // Mối quan hệ: Một tin nhắn thuộc về một cuộc trò chuyện
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Mối quan hệ: Một tin nhắn có một người gửi
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scope giúp lấy tin nhắn không bị xóa
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }
}

