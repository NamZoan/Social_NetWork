<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallParticipant extends Model
{
    use HasFactory;

    public const STATE_INVITED = 'invited';
    public const STATE_RINGING = 'ringing';
    public const STATE_JOINED = 'joined';
    public const STATE_LEFT = 'left';
    public const STATE_DECLINED = 'declined';
    public const STATE_FAILED = 'failed';

    protected $fillable = [
        'call_id',
        'user_id',
        'role',
        'state',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at'   => 'datetime',
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeJoined($query)
    {
        return $query->where('state', self::STATE_JOINED);
    }
}
