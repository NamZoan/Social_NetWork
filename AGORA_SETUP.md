# Hướng dẫn cấu hình Agora SDK cho Video Call

## 1. Đăng ký tài khoản Agora

1. Truy cập https://www.agora.io/
2. Đăng ký tài khoản miễn phí
3. Tạo một dự án mới trong Agora Console
4. Lấy **App ID** và **App Certificate** từ dashboard

## 2. Cấu hình biến môi trường

Thêm các biến sau vào file `.env`:

```env
AGORA_APP_ID=your_app_id_here
AGORA_APP_CERTIFICATE=your_app_certificate_here
```

## 3. Cấu hình đã được thêm vào

- ✅ `config/services.php` - Đã thêm cấu hình Agora
- ✅ `app/Http/Controllers/CallController.php` - Đã thêm method `getAgoraToken()`
- ✅ `routes/web.php` - Đã thêm route `/calls/{id}/agora-token`
- ✅ `resources/js/Components/Messages/VideoCall.vue` - Đã tích hợp Agora SDK
- ✅ `resources/js/Components/Messages/CallIncomingListener.vue` - Đã cập nhật

## 4. Cách sử dụng

### Token Generation

Hệ thống đã được tích hợp với package `yasserbelhimer/agora-access-token-generator` để tự động generate token từ backend.

Token sẽ được tạo tự động khi gọi API `/calls/{id}/agora-token` với:
- **App ID**: Từ cấu hình `AGORA_APP_ID`
- **App Certificate**: Từ cấu hình `AGORA_APP_CERTIFICATE`
- **Channel Name**: `call_{call_id}`
- **UID**: ID của user hiện tại
- **Role**: Publisher (có quyền publish audio/video)
- **Expire Time**: 24 giờ

### Package đã được cài đặt

Package `yasserbelhimer/agora-access-token-generator` đã được cài đặt và sử dụng trong `CallController::getAgoraToken()`.

## 5. Các tính năng đã được tích hợp

- ✅ Video call 1-1
- ✅ Audio call (có thể bật/tắt camera)
- ✅ Bật/tắt microphone
- ✅ Bật/tắt camera
- ✅ Chia sẻ màn hình
- ✅ Hiển thị trạng thái kết nối
- ✅ Đếm thời gian cuộc gọi
- ✅ Xử lý khi người dùng rời khỏi cuộc gọi

## 6. Lưu ý

- Agora SDK đã được cài đặt trong `package.json` (`agora-rtc-sdk-ng: ^4.24.1`)
- Đảm bảo đã chạy `npm install` để cài đặt dependencies
- Token có thể để null trong development mode nếu app được cấu hình ở chế độ test
- Trong production, nên sử dụng token để bảo mật

## 7. Troubleshooting

### Lỗi "Agora App ID không được cấu hình"
- Kiểm tra file `.env` có `AGORA_APP_ID` chưa
- Chạy `php artisan config:clear` để clear cache

### Lỗi kết nối
- Kiểm tra App ID và Certificate có đúng không
- Kiểm tra network/firewall có chặn kết nối đến Agora không
- Xem console log để biết chi tiết lỗi

### Video không hiển thị
- Kiểm tra quyền truy cập camera/microphone
- Kiểm tra browser có hỗ trợ WebRTC không
- Xem console log để debug

