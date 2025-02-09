<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar',
        'phone',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Boot function để xử lý sự kiện khi tạo user
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->username = self::generateUniqueUsername($user->name);
        });
    }

    // Hàm tạo username duy nhất
    protected static function generateUniqueUsername($name)
    {
        // Chuyển tên thành slug (bỏ dấu, dấu cách, ký tự đặc biệt)
        $baseUsername = Str::slug($name, '');

        // Thêm số ngẫu nhiên nếu cần
        $username = $baseUsername;
        $counter = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
