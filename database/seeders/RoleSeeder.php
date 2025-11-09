<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'administrator',
                'display_name' => 'Quản trị viên',
                'description' => 'Có toàn quyền quản trị hệ thống',
                'permissions' => [
                    'manage_users',
                    'manage_roles',
                    'manage_posts',
                    'manage_groups',
                    'manage_meta_business',
                    'view_analytics',
                    'moderate_content',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'editor',
                'display_name' => 'Biên tập viên',
                'description' => 'Có quyền chỉnh sửa và quản lý nội dung',
                'permissions' => [
                    'manage_posts',
                    'edit_posts',
                    'delete_posts',
                    'manage_groups',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'advertiser',
                'display_name' => 'Nhà quảng cáo',
                'description' => 'Có quyền quản lý quảng cáo và Meta Business Suite',
                'permissions' => [
                    'manage_meta_business',
                    'create_ads',
                    'manage_ads',
                    'view_analytics',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'analyst',
                'display_name' => 'Nhà phân tích',
                'description' => 'Có quyền xem và phân tích dữ liệu',
                'permissions' => [
                    'view_analytics',
                    'export_data',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Người kiểm duyệt',
                'description' => 'Có quyền kiểm duyệt nội dung và thành viên',
                'permissions' => [
                    'moderate_content',
                    'approve_posts',
                    'reject_posts',
                    'manage_users',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}


