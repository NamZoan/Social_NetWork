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
    public const ROLE_ANALYST = 'analyst';

    /**
     * Danh sách các roles có sẵn
     */
    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_ANALYST,
        ];
    }

    /**
     * Lấy thông tin chi tiết về role
     */
    public static function getRoleInfo(): array
    {
        return [
            self::ROLE_ADMIN => [
                'name' => 'Admin',
                'description' => 'Quản lý toàn bộ trang',
                'color' => '#ef4444',
                'icon' => 'bx-crown',
                'permissions' => [
                    'manage_admins',
                    'edit_page',
                    'delete_page',
                    'create_posts',
                    'edit_posts',
                    'delete_posts',
                    'manage_comments',
                    'view_insights',
                    'view_analytics',
                    'export_reports',
                ]
            ],
            self::ROLE_EDITOR => [
                'name' => 'Editor',
                'description' => 'Quản lý nội dung và bài viết',
                'color' => '#3b82f6',
                'icon' => 'bx-pencil',
                'permissions' => [
                    'create_posts',
                    'edit_posts',
                    'delete_posts',
                    'manage_comments',
                    'edit_page_info',
                ]
            ],
            self::ROLE_ANALYST => [
                'name' => 'Analyst',
                'description' => 'Xem báo cáo và phân tích',
                'color' => '#f59e0b',
                'icon' => 'bx-bar-chart',
                'permissions' => [
                    'view_insights',
                    'view_analytics',
                    'export_reports',
                ]
            ],
        ];
    }

    /**
     * Kiểm tra role có quyền cụ thể không
     */
    public static function roleHasPermission(string $role, string $permission): bool
    {
        $roleInfo = self::getRoleInfo()[$role] ?? null;
        if (!$roleInfo) {
            return false;
        }
        return in_array($permission, $roleInfo['permissions'] ?? []);
    }
}
