<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'media_type',
        'media_url'
    ];

    /**
     * Quan hệ với Post (media thuộc về một bài viết).
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Quan hệ với User (media thuộc về một người dùng).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
