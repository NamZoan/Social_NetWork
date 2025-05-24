<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    // Nếu bảng không phải mặc định plural 'notifications', khai báo protected $table
    // protected $table = 'notifications';

    // Khóa chính là 'id' kiểu bigint unsigned
    protected $primaryKey = 'id';

    // Kiểu dữ liệu khóa chính là unsigned bigint, auto-increment mặc định
    protected $keyType = 'int';

    // Tự động tăng id
    public $incrementing = true;

    // Bảng có trường timestamps? Có, nhưng bạn chỉ có created_at, không có updated_at
    // Vậy nên tắt timestamps mặc định và khai báo created_at riêng
    public $timestamps = false;

    // Định nghĩa các trường được phép gán hàng loạt (mass assign)
    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'reference_type',
        'sender_id',
        'message',
        'created_at',
        'is_read',
        'read_at',
        'action_url',
    ];

    // Định nghĩa các trường kiểu ngày tháng (Carbon instances)
    protected $dates = [
        'created_at',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Định nghĩa quan hệ tới user nhận notification
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Định nghĩa quan hệ tới user gửi notification (sender)
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the message that owns the notification.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
