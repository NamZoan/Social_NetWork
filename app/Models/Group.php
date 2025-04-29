<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'creator_id',
        'privacy_setting',
        'cover_photo_url',
        'group_type',
        'post_approval_required',
    ];

    // Nếu bạn có quan hệ với User:
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Relationship with members
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withPivot('role', 'joined_at', 'membership_status')
            ->withTimestamps();
    }

    public function getMembersCountAttribute()
    {
        return $this->members()
            ->where('membership_status', 'active')
            ->count();
    }

    // Relationship with posts
    public function posts()
    {
        return $this->hasMany(Post::class, 'group_id');
    }
}
