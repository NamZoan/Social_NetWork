<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInteraction extends Model
{
    use HasFactory;

    // Tên bảng (tùy chọn nếu tên model khớp chuẩn Laravel)
    protected $table = 'user_interactions';

    // Các trường được phép thêm dữ liệu (Mass Assignment)
    protected $fillable = [
        'user_id',
        'post_id',
        'interaction_type',
        // Nếu sau này bạn thêm cột duration, nhớ thêm vào đây
        // 'duration_ms', 
    ];

    /**
     * Quan hệ với User (User thực hiện hành động)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với Post (Bài viết được tương tác)
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}