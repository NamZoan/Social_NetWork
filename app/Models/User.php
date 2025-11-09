<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use GetStream\StreamLaravel\Facades\FeedManager;
use GetStream\StreamLaravel\Eloquent\ActivityTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
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
            ->withPivot('role', 'joined_at', 'membership_status', 'active');
    }

    public function friendIds()
    {
        $ids1 = Friendship::where('user_id_1', $this->id)
            ->where('status', 'accepted')
            ->pluck('user_id_2')->toArray();
        $ids2 = Friendship::where('user_id_2', $this->id)
            ->where('status', 'accepted')
            ->pluck('user_id_1')->toArray();
        return array_unique(array_merge($ids1, $ids2));
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
        if (!$user)
            return false;

        return $this->friends()
            ->where(function ($query) use ($user) {
                $query->where('user_id_1', $user->id)
                    ->orWhere('user_id_2', $user->id);
            })
            ->exists();
    }
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_members', 'user_id', 'conversation_id')
            ->withPivot('role', 'joined_at');
    }

    /**
     * Mối quan hệ với roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Mối quan hệ với Meta Accounts
     */
    public function metaAccounts()
    {
        return $this->hasMany(MetaAccount::class, 'user_id');
    }

    /**
     * Kiểm tra xem user có role cụ thể không
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->where('is_active', true)->exists();
    }

    /**
     * Kiểm tra xem user có bất kỳ role nào trong danh sách không
     */
    public function hasAnyRole(array $roleNames)
    {
        return $this->roles()->whereIn('name', $roleNames)->where('is_active', true)->exists();
    }

    /**
     * Kiểm tra xem user có tất cả các role trong danh sách không
     */
    public function hasAllRoles(array $roleNames)
    {
        $userRoles = $this->roles()->where('is_active', true)->pluck('name')->toArray();
        return count(array_intersect($roleNames, $userRoles)) === count($roleNames);
    }

    /**
     * Gán role cho user
     */
    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->where('is_active', true)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Xóa role khỏi user
     */
    public function removeRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Kiểm tra xem user có quyền cụ thể không (thông qua roles)
     */
    public function hasPermission($permission)
    {
        return $this->roles()->where('is_active', true)->get()->filter(function ($role) use ($permission) {
            return $role->hasPermission($permission);
        })->isNotEmpty();
    }
}
