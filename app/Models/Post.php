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

    protected $fillable = [
        'user_id',
        'content',
        'privacy_setting',
        'allow_comments',
        'original_post_id',
        'group_id'
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
    public function shares(): HasMany
    {
        return $this->hasMany(Post::class, 'original_post_id');
    }
}
