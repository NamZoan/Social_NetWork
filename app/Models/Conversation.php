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
        'is_active'
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
}

