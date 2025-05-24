<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'post_id', 'user_id', 'content', 'parent_comment_id',
        'is_hidden', 'attachment_url', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'is_hidden' => 'boolean'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    // Scope để lấy comments gốc (không phải reply)
    public function scopeRootComments(Builder $query): Builder
    {
        return $query->whereNull('parent_comment_id');
    }

    // Scope để lấy comments theo thứ tự mới nhất
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Scope để lấy comments chưa bị ẩn
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    // Lấy tất cả replies của một comment
    public function getAllReplies()
    {
        return $this->replies()
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Kiểm tra xem comment có phải là reply không
    public function isReply(): bool
    {
        return !is_null($this->parent_comment_id);
    }

    // Lấy số lượng replies của comment
    public function getRepliesCount(): int
    {
        return $this->replies()->count();
    }

    // Ẩn/hiện comment
    public function toggleVisibility(): bool
    {
        $this->is_hidden = !$this->is_hidden;
        return $this->save();
    }
}