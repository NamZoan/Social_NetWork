<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id_1', 'user_id_2')
            ->union($this->belongsToMany(User::class, 'friendships', 'user_id_2', 'user_id_1'));
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id')
            ->withPivot('role', 'joined_at', 'membership_status');
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class, 'user_id');
    }

    /**
     * Kiểm tra xem user hiện tại có phải là bạn của user khác không
     */
    public function isFriendWith($user)
    {
        if (!$user) return false;
        
        return $this->friends()
            ->where(function($query) use ($user) {
                $query->where('user_id_1', $user->id)
                    ->orWhere('user_id_2', $user->id);
            })
            ->exists();
    }
}
