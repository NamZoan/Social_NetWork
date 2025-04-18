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

    // Nếu bạn muốn thêm mối quan hệ với bài viết hoặc thành viên nhóm thì thêm ở đây:
    // public function posts()
    // {
    //     return $this->hasMany(GroupPost::class);
    // }
}
