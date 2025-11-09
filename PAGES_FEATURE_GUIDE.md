# Hướng dẫn Tính năng Pages (Trang)

## Tổng quan

Tính năng Pages cho phép người dùng tạo và quản lý các trang công khai, tương tự như Facebook Pages. Người dùng có thể:
- Tạo trang với ảnh đại diện và ảnh bìa
- Đăng bài lên trang
- Theo dõi/ bỏ theo dõi trang
- Xem phân tích (insights) nếu là admin
- Quản lý trang với các quyền admin khác nhau

## Cài đặt

### 1. Chạy Migrations

```bash
php artisan migrate
```

Các migrations sẽ tạo:
- `pages` - Bảng lưu thông tin trang
- `page_admins` - Bảng quản lý admin của trang
- `page_followers` - Bảng theo dõi trang
- Thêm `page_id` vào bảng `posts`

### 2. Cấu trúc Database

#### Bảng `pages`
- `id` - ID trang
- `creator_id` - User tạo trang
- `name` - Tên trang
- `username` - Username của trang (unique)
- `category` - Danh mục
- `description` - Mô tả
- `profile_picture_url` - URL ảnh đại diện
- `cover_photo_url` - URL ảnh bìa
- `website`, `phone`, `email` - Thông tin liên hệ
- `location` - JSON: địa chỉ, tọa độ
- `hours_of_operation` - JSON: giờ mở cửa
- `verified` - Trang đã được xác minh
- `follower_count` - Số người theo dõi
- `is_active` - Trạng thái hoạt động

#### Bảng `page_admins`
- `page_id` - ID trang
- `user_id` - ID user
- `role` - Vai trò: admin, editor, moderator, analyst, advertiser

#### Bảng `page_followers`
- `page_id` - ID trang
- `user_id` - ID user
- `notification_settings` - Cài đặt thông báo: all, highlights, none

## API Endpoints

### Pages Routes

```php
GET  /pages/create              - Form tạo trang mới
POST /pages                     - Tạo trang mới
GET  /pages/{identifier}        - Xem trang (username hoặc ID)
POST /pages/{page}/update       - Cập nhật trang (chỉ admin)
POST /pages/{page}/follow       - Theo dõi/ bỏ theo dõi
GET  /pages/{page}/insights     - Xem insights (chỉ admin)
GET  /pages/{page}/posts        - Lấy danh sách posts (pagination)
```

### Post với Page

```php
POST /posts
{
    "content": "Nội dung bài đăng",
    "page_id": 1,  // ID của trang
    "privacy_setting": "public",
    "images[]": [file1, file2, ...]  // File ảnh
}
```

## Frontend Components

### 1. PageHeader.vue
- Hiển thị cover photo và profile picture
- Thông tin trang (tên, username, category, followers)
- Nút Follow/Following cho khách
- Nút Edit và View as Guest cho admin
- Nút Like, Message, Share cho khách

### 2. PageNavigationTabs.vue
- Tabs: Trang chủ, Giới thiệu, Bài viết, Ảnh, Video, Cộng đồng
- Tabs admin: Phân tích, Cài đặt

### 3. PagePostCreator.vue
- Khung tạo bài đăng cho trang
- Upload ảnh/video
- Preview ảnh trước khi đăng
- Validation và loading states

### 4. PageInsights.vue
- Hiển thị metrics (Reach, Engagement, Followers, Posts)
- Biểu đồ (cần tích hợp Chart.js)
- Top posts grid

### 5. Pages/Show.vue
- Trang chi tiết trang
- Tích hợp tất cả components
- Quản lý tabs và navigation

### 6. Pages/Create.vue
- Form tạo trang mới
- Upload ảnh đại diện và ảnh bìa
- Validation

### 7. Pages/Insights.vue
- Trang insights riêng
- Sử dụng PageInsights component

## Sử dụng

### Tạo Page mới

1. Truy cập `/pages/create`
2. Điền thông tin:
   - Tên trang (bắt buộc)
   - Username (tùy chọn, tự động tạo nếu không có)
   - Danh mục
   - Mô tả
   - Ảnh đại diện
   - Ảnh bìa
3. Nhấn "Tạo trang"
4. Tự động chuyển đến trang vừa tạo

### Đăng bài lên Page

1. Vào trang Page (chỉ admin mới thấy Post Creator)
2. Nhập nội dung hoặc chọn ảnh
3. Nhấn "Đăng"

### Theo dõi Page

1. Vào trang Page
2. Nhấn nút "Theo dõi"
3. Có thể bỏ theo dõi bằng cách nhấn lại

### Xem Insights

1. Vào trang Page (phải là admin)
2. Click tab "Phân tích"
3. Hoặc truy cập trực tiếp `/pages/{page_id}/insights`

## Permissions

### Admin Roles
- `admin` - Toàn quyền
- `editor` - Chỉnh sửa nội dung
- `moderator` - Kiểm duyệt
- `analyst` - Xem insights
- `advertiser` - Quản lý quảng cáo

### Quyền Admin
- Chỉnh sửa trang
- Đăng bài
- Xem insights
- Quản lý admins khác
- Xem với tư cách khách

## Tích hợp với Posts

Posts có thể thuộc về:
- User (bài viết cá nhân)
- Group (bài viết trong nhóm)
- Page (bài viết trên trang) - **MỚI**

Posts trên Page:
- Luôn có `privacy_setting = 'public'`
- Hiển thị tên trang thay vì tên user
- Có thể có nhiều ảnh/video

## Lưu ý

1. **Storage**: Ảnh được lưu trong `storage/app/public/pages/`
   - `profile_pictures/` - Ảnh đại diện
   - `cover_photos/` - Ảnh bìa

2. **Username**: Tự động tạo từ tên trang nếu không có
   - Format: slug(tên) + số (nếu trùng)

3. **Followers Count**: Tự động cập nhật khi có người theo dõi/bỏ theo dõi

4. **Insights**: Cần thêm field `reach` vào bảng posts để tính chính xác

5. **Charts**: Cần cài đặt Chart.js để hiển thị biểu đồ
   ```bash
   npm install chart.js
   ```

## Troubleshooting

### Lỗi: "Bạn không có quyền đăng bài trên trang này"
- Kiểm tra user có phải là admin của page không
- Sử dụng `$page->isAdmin($user_id)` để kiểm tra

### Lỗi: "Trang không tồn tại"
- Kiểm tra page_id có đúng không
- Kiểm tra page có `is_active = true` không

### Posts không hiển thị
- Kiểm tra posts có `page_id` đúng không
- Kiểm tra query trong `UserPageController::show()`

## Frontend Variables

### Props của PageHeader
```javascript
{
  page: Object,           // Thông tin trang
  isFollowing: Boolean,   // User đang theo dõi?
  isAdmin: Boolean,       // User là admin?
  currentUser: Object     // User hiện tại
}
```

### Props của PagePostCreator
```javascript
{
  page: Object  // Thông tin trang
}
```

### Props của PageInsights
```javascript
{
  page: Object,      // Thông tin trang
  insights: Object   // Dữ liệu insights
}
```

## Next Steps

1. Tích hợp Chart.js cho biểu đồ
2. Thêm tính năng Live streaming
3. Thêm tính năng Events
4. Thêm tính năng Quảng cáo
5. Thêm notification settings cho followers
6. Thêm tính năng verified badge


