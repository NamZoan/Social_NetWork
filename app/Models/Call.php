<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    use HasFactory;

    /**
     * Các trạng thái hợp lệ (tham khảo controller & migration).
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RINGING = 'ringing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ENDED = 'ended';
    public const STATUS_MISSED = 'missed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'creator_id',
        'type',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /**
     * Người tạo cuộc gọi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Danh sách participant (caller/callee) của cuộc gọi.
     */
    public function participants(): HasMany
    {
        return $this->hasMany('App\Models\CallParticipant');
    }

    /**
     * Những participant đang ở trạng thái đã tham gia (joined).
     */
    public function joinedParticipants(): HasMany
    {
        return $this->participants()->where('state', 'joined');
    }

    /**
     * Scope các cuộc gọi đang mở (chưa kết thúc).
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [self::STATUS_ENDED, self::STATUS_FAILED, self::STATUS_MISSED]);
    }
}
