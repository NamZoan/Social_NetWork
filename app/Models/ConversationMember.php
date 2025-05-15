<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMember extends Model
{
    protected $table = 'conversation_members';

    public $timestamps = false; // Vì bảng không có created_at, updated_at

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'joined_at',
    ];

    // Quan hệ đến Conversation
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    // Quan hệ đến User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
