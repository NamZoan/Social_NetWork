<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedPost extends Model
{
    protected $table = 'related_posts';

    protected $fillable = [
        'post_id',
        'related_post_id',
        'similarity_score'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function related()
    {
        return $this->belongsTo(Post::class, 'related_post_id');
    }
}
