<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasFactory;

    const PRIVACY_PUBLIC = 'public';
    const PRIVACY_FRIENDS = 'friends';
    const PRIVACY_PRIVATE = 'private';

    protected $fillable = [
        'user_id',
        'content',
        'privacy_setting',
        'allow_comments',
        'original_post_id',
        'group_id',
        'page_id'
    ];

    /**
     * Quan hệ với User (một bài viết thuộc về một người dùng).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với Media (một bài viết có nhiều media).
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Quan hệ với Images (alias cho media với type là image).
     */
    public function images()
    {
        return $this->media()->where('media_type', 'image');
    }

    // Quan hệ với Likes (polymorphic)
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'content', 'content_type', 'content_id');
    }
    public function isLikedBy($user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    // Quan hệ với Comments
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    // Lấy comments gốc của bài post
    public function getRootComments()
    {
        return $this->comments()
            ->rootComments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    // Lấy số lượng comments của bài post (bao gồm cả replies)
    public function getTotalCommentsCount(): int
    {
        return $this->comments()->count() + $this->comments()->withCount('replies')->get()->sum('replies_count');
    }

    // Lấy số lượng comments gốc của bài post
    public function getRootCommentsCount(): int
    {
        return $this->comments()->rootComments()->count();
    }

    /**
     * Quan hệ với Post (bài viết gốc nếu đây là bài chia sẻ lại).
     */
    public function originalPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'original_post_id');
    }

    /**
     * Quan hệ với Group (nếu bài viết thuộc về một nhóm).
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Quan hệ với Page (nếu bài viết thuộc về một trang).
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
    public function shares(): HasMany
    {
        return $this->hasMany(Post::class, 'original_post_id');
    }

    /**
     * Kiểm tra xem người dùng có quyền xem bài viết không
     */
    public function canView($user)
    {
        // Nếu không có user được truyền vào, chỉ cho phép xem bài viết public
        if (!$user) {
            return $this->privacy_setting === self::PRIVACY_PUBLIC;
        }

        // Người đăng bài luôn có quyền xem
        if ($this->user_id === $user->id) {
            return true;
        }

        switch ($this->privacy_setting) {
            case self::PRIVACY_PUBLIC:
                return true;
            case self::PRIVACY_FRIENDS:
                return $this->user->isFriendWith($user);
            case self::PRIVACY_PRIVATE:
                return false;
            default:
                return false;
        }
    }

    // Kiểm tra xem user có thể comment không
    public function canComment($user): bool
    {
        if (!$this->allow_comments) {
            return false;
        }
        return $this->canView($user);
    }

    // Lấy comments mới nhất của bài post
    public function getLatestComments($limit = 5)
    {
        return $this->comments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
