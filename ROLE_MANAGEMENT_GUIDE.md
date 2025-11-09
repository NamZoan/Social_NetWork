# Hướng dẫn Quản lý Phân quyền và Meta Business Suite

## Tổng quan

Hệ thống đã được tích hợp với:
1. **Hệ thống phân quyền** với 5 vai trò: Quản trị viên, Biên tập viên, Nhà quảng cáo, Nhà phân tích, Người kiểm duyệt
2. **Kết nối Meta Business Suite** để quản lý nhiều trang Facebook

## Cài đặt

### 1. Chạy Migrations

```bash
php artisan migrate
```

### 2. Seed Roles mặc định

```bash
php artisan db:seed --class=RoleSeeder
```

Hoặc trong `DatabaseSeeder.php`:

```php
$this->call([
    RoleSeeder::class,
]);
```

### 3. Cấu hình Meta API

Thêm vào file `.env`:

```env
META_CLIENT_ID=your_facebook_app_id
META_CLIENT_SECRET=your_facebook_app_secret
META_REDIRECT_URI=http://your-domain.com/meta-business/callback
```

## Các Vai trò

### 1. Quản trị viên (Administrator)
- Toàn quyền quản trị hệ thống
- Quản lý users, roles, posts, groups
- Quản lý Meta Business Suite
- Xem analytics và kiểm duyệt nội dung

### 2. Biên tập viên (Editor)
- Quản lý và chỉnh sửa posts
- Quản lý groups
- Xóa posts

### 3. Nhà quảng cáo (Advertiser)
- Quản lý Meta Business Suite
- Tạo và quản lý quảng cáo
- Xem analytics

### 4. Nhà phân tích (Analyst)
- Xem analytics
- Xuất dữ liệu

### 5. Người kiểm duyệt (Moderator)
- Kiểm duyệt nội dung
- Phê duyệt/từ chối posts
- Quản lý users

## Sử dụng

### Gán vai trò cho User

```php
use App\Models\User;

$user = User::find(1);
$user->assignRole('administrator');
```

### Kiểm tra vai trò

```php
if ($user->hasRole('administrator')) {
    // User là admin
}

if ($user->hasAnyRole(['editor', 'moderator'])) {
    // User là editor hoặc moderator
}
```

### Kiểm tra quyền

```php
if ($user->hasPermission('manage_users')) {
    // User có quyền quản lý users
}
```

### Sử dụng Middleware

```php
// Kiểm tra role
Route::get('/admin', function () {
    //
})->middleware('role:administrator');

// Kiểm tra nhiều roles
Route::get('/admin', function () {
    //
})->middleware('role:administrator,editor');

// Kiểm tra permission
Route::get('/users', function () {
    //
})->middleware('permission:manage_users');
```

## Meta Business Suite

### Kết nối tài khoản

1. Truy cập `/meta-business/connect`
2. Đăng nhập với Facebook và cấp quyền
3. Hệ thống sẽ tự động lấy danh sách pages và lưu vào database

### Đồng bộ Pages

```php
$account = MetaAccount::find(1);
$controller = new MetaBusinessController();
$controller->sync($account);
```

### Lấy Insights

```php
$page = MetaPage::find(1);
$insights = Http::get("/meta-business/pages/{$page->id}/insights");
```

## Routes

### Admin Routes (yêu cầu role: administrator)
- `GET /admin/roles` - Danh sách roles
- `POST /admin/roles` - Tạo role mới
- `GET /admin/roles/{role}` - Chi tiết role
- `PUT /admin/roles/{role}` - Cập nhật role
- `DELETE /admin/roles/{role}` - Xóa role
- `POST /admin/roles/{role}/users/{user}/assign` - Gán role cho user
- `DELETE /admin/roles/{role}/users/{user}/remove` - Xóa role khỏi user

### Meta Business Routes (yêu cầu role: administrator hoặc advertiser)
- `GET /meta-business` - Danh sách Meta Accounts
- `GET /meta-business/connect` - Kết nối Meta Account
- `GET /meta-business/callback` - OAuth callback
- `GET /meta-business/{account}` - Chi tiết account và pages
- `POST /meta-business/{account}/sync` - Đồng bộ pages
- `POST /meta-business/{account}/disconnect` - Ngắt kết nối
- `GET /meta-business/pages/{page}/insights` - Lấy insights của page

## Lưu ý

1. Đảm bảo đã cấu hình đúng Meta App ID và Secret trong `.env`
2. Redirect URI phải khớp với cấu hình trong Facebook App
3. Cần cấp đầy đủ permissions: `business_management`, `pages_read_engagement`, `pages_show_list`, `pages_manage_metadata`
4. Access tokens có thể hết hạn, cần implement token refresh logic

## Frontend Components

Cần tạo các Vue components trong `resources/js/Pages/Admin/Roles/` và `resources/js/Pages/MetaBusiness/` để hiển thị UI.


