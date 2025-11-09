<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'name',
        'username',
        'category',
        'description',
        'profile_picture_url',
        'cover_photo_url',
        'website',
        'phone',
        'email',
        'location',
        'hours_of_operation',
        'verified',
        'follower_count',
        'is_active',
    ];

    protected $casts = [
        'location' => 'array',
        'hours_of_operation' => 'array',
        'verified' => 'boolean',
        'is_active' => 'boolean',
        'follower_count' => 'integer',
    ];

    /**
     * Mối quan hệ với User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Mối quan hệ với admins
     */
    public function admins()
    {
        return $this->belongsToMany(User::class, 'page_admins', 'page_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Mối quan hệ với followers
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'page_followers', 'page_id', 'user_id')
            ->withPivot('notification_settings')
            ->withTimestamps();
    }

    /**
     * Mối quan hệ với posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'page_id');
    }

    /**
     * Kiểm tra xem user có phải là admin của page không
     */
    public function isAdmin($userId)
    {
        return $this->admins()->where('user_id', $userId)->exists();
    }

    /**
     * Kiểm tra xem user có quyền admin cụ thể không
     */
    public function hasAdminRole($userId, $role)
    {
        return $this->admins()
            ->where('user_id', $userId)
            ->wherePivot('role', $role)
            ->exists();
    }

    /**
     * Kiểm tra xem user có đang theo dõi page không
     */
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('user_id', $userId)->exists();
    }

    /**
     * Tăng số lượng followers
     */
    public function incrementFollowers()
    {
        $this->increment('follower_count');
    }

    /**
     * Giảm số lượng followers
     */
    public function decrementFollowers()
    {
        $this->decrement('follower_count');
    }

    /**
     * Lấy URL ảnh đại diện
     */
    public function getProfilePictureAttribute()
    {
        return $this->profile_picture_url
            ? asset($this->profile_picture_url)
            : asset('/images/web/users/avatar.jpg');
    }

    /**
     * Lấy URL ảnh bìa
     */
    public function getCoverPhotoAttribute()
    {
        return $this->cover_photo_url
            ? asset($this->cover_photo_url)
            : asset('/images/web/default-cover.jpg');
    }

    /**
     * Lấy URL trang
     */
    public function getUrlAttribute()
    {
        return $this->username
            ? route('pages.show', $this->username)
            : route('pages.show', $this->id);
    }
}


