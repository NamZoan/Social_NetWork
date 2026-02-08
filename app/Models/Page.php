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

    protected $appends = ['avatar_url', 'cover_url'];

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
            ->using(PageAdmin::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Mối quan hệ với followers
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'page_followers', 'page_id', 'user_id')
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

    public function getRoleForUser($userId): ?string
    {
        $admin = $this->admins()->where('user_id', $userId)->first();
        return $admin?->pivot?->role;
    }

    public function canUpdateBy($userId): bool
    {
        $role = $this->getRoleForUser($userId);
        return in_array($role, [PageAdmin::ROLE_ADMIN, PageAdmin::ROLE_EDITOR], true);
    }

    public function canManageAdmins($userId): bool
    {
        return $this->getRoleForUser($userId) === PageAdmin::ROLE_ADMIN;
    }

    /**
     * Kiểm tra quyền tạo bài viết
     */
    public function canCreatePost($userId): bool
    {
        $role = $this->getRoleForUser($userId);
        return in_array($role, [PageAdmin::ROLE_ADMIN, PageAdmin::ROLE_EDITOR], true);
    }

    /**
     * Kiểm tra quyền chỉnh sửa bài viết
     */
    public function canEditPost($userId): bool
    {
        $role = $this->getRoleForUser($userId);
        return in_array($role, [PageAdmin::ROLE_ADMIN, PageAdmin::ROLE_EDITOR], true);
    }

    /**
     * Kiểm tra quyền xóa bài viết
     */
    public function canDeletePost($userId): bool
    {
        $role = $this->getRoleForUser($userId);
        return in_array($role, [PageAdmin::ROLE_ADMIN, PageAdmin::ROLE_EDITOR], true);
    }

    /**
     * Kiểm tra quyền quản lý bình luận
     */
    public function canManageComments($userId): bool
    {
        $role = $this->getRoleForUser($userId);
        return in_array($role, [PageAdmin::ROLE_ADMIN, PageAdmin::ROLE_EDITOR], true);
    }

    /**
     * Kiểm tra quyền xem insights
     */
    public function canViewInsights($userId): bool
    {
        $role = $this->getRoleForUser($userId);
        return in_array($role, [PageAdmin::ROLE_ADMIN, PageAdmin::ROLE_ANALYST], true);
    }

    /**
     * Kiểm tra quyền xóa trang
     */
    public function canDeletePage($userId): bool
    {
        return $this->getRoleForUser($userId) === PageAdmin::ROLE_ADMIN;
    }

    /**
     * Kiểm tra quyền dựa trên permission string
     */
    public function hasPermission($userId, string $permission): bool
    {
        $role = $this->getRoleForUser($userId);
        if (!$role) {
            return false;
        }
        return PageAdmin::roleHasPermission($role, $permission);
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
     * Accessor: Lấy URL ảnh đại diện với default
     */
    public function getProfilePictureUrlAttribute($value)
    {
        if (!$value) {
            return '/images/client/pages/default-page.png';
        }
        
        // Xử lý URL
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        
        if (str_starts_with($value, '/')) {
            return $value;
        }
        
        // Xóa storage/ prefix nếu có
        $normalized = preg_replace('#^storage/(app/)?public/#i', '', $value);
        $normalized = preg_replace('#^storage/#i', '', $normalized);
        $normalized = ltrim($normalized, '/');
        
        return '/' . $normalized;
    }

    /**
     * Accessor: Lấy URL ảnh bìa với default
     */
    public function getCoverPhotoUrlAttribute($value)
    {
        if (!$value) {
            return '/images/web/users/cover/cover-1.gif';
        }
        
        // Xử lý URL
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        
        if (str_starts_with($value, '/')) {
            return $value;
        }
        
        // Xóa storage/ prefix nếu có
        $normalized = preg_replace('#^storage/(app/)?public/#i', '', $value);
        $normalized = preg_replace('#^storage/#i', '', $normalized);
        $normalized = ltrim($normalized, '/');
        
        return '/' . $normalized;
    }

    /**
     * Mutator: Tạo avatar_url từ profile_picture_url
     */
    public function getAvatarUrlAttribute()
    {
        return $this->profile_picture_url_attribute ?: '/images/web/users/avatar.jpg';
    }

    /**
     * Mutator: Tạo cover_url từ cover_photo_url
     */
    public function getCoverUrlAttribute()
    {
        return $this->cover_photo_url_attribute ?: '/images/web/default-cover.jpg';
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
}

