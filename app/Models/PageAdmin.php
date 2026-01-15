<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PageAdmin extends Pivot
{
    protected $table = 'page_admins';

    protected $fillable = [
        'page_id',
        'user_id',
        'role',
    ];

    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_ANALYST = 'analyst';
    public const ROLE_ADVERTISER = 'advertiser';

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_MODERATOR,
            self::ROLE_ANALYST,
            self::ROLE_ADVERTISER,
        ];
    }
}
