# Hướng dẫn Debug Video Call

## Các bước kiểm tra

### 1. Kiểm tra Console Logs

Mở Browser Console (F12) và tìm các log bắt đầu bằng `[VideoCall]` hoặc `[Header]`:

- `[Header] Starting video call...` - Khi bấm nút gọi video
- `[VideoCall] Component mounted...` - Khi component được render
- `[VideoCall] Initializing Agora client...` - Khi khởi tạo Agora
- `[VideoCall] Agora config response...` - Response từ API
- `[VideoCall] Creating local tracks...` - Khi tạo audio/video tracks
- `[VideoCall] Joining channel...` - Khi join Agora channel

### 2. Kiểm tra lỗi phổ biến

#### Lỗi: "Agora App ID không được cấu hình"
**Nguyên nhân:** Chưa cấu hình `AGORA_APP_ID` trong `.env`

**Giải pháp:**
```bash
# Thêm vào file .env
AGORA_APP_ID=your_app_id_here
AGORA_APP_CERTIFICATE=your_app_certificate_here

# Sau đó chạy
php artisan config:clear
```

#### Lỗi: "Error joining channel"
**Nguyên nhân có thể:**
- Token không hợp lệ
- App ID/Certificate sai
- Network issue

**Giải pháp:**
- Kiểm tra token trong response của API `/calls/{id}/agora-token`
- Kiểm tra App ID và Certificate trong Agora Console

#### Lỗi: "Không tìm thấy camera hoặc micro"
**Nguyên nhân:** Browser chưa được cấp quyền truy cập camera/microphone

**Giải pháp:**
- Cho phép browser truy cập camera/microphone
- Kiểm tra Settings > Privacy > Camera/Microphone

#### Component không hiển thị
**Kiểm tra:**
1. Xem có log `[Header] VideoCall component should be visible now` không?
2. Kiểm tra `showVideoCall` có được set thành `true` không?
3. Kiểm tra `currentCallId` có giá trị không?

### 3. Kiểm tra Network Requests

Trong Browser DevTools > Network tab, kiểm tra:

1. **POST /calls/invite** - Tạo cuộc gọi
   - Status: 201 Created
   - Response: `{ id: number }`

2. **GET /calls/{id}/agora-token** - Lấy token
   - Status: 200 OK
   - Response: `{ app_id, channel, uid, token }`

3. **GET /calls/{id}** - Lấy thông tin cuộc gọi
   - Status: 200 OK
   - Response: `{ call: {...}, participants: [...] }`

### 4. Kiểm tra Agora Configuration

1. Đăng nhập vào Agora Console: https://console.agora.io/
2. Kiểm tra App ID và App Certificate
3. Đảm bảo App đã được kích hoạt

### 5. Test từng bước

#### Bước 1: Test API tạo cuộc gọi
```javascript
// Trong Browser Console
axios.post('/calls/invite', { user_id: 2 })
  .then(res => console.log('Call created:', res.data))
  .catch(err => console.error('Error:', err))
```

#### Bước 2: Test API lấy token
```javascript
// Thay {call_id} bằng ID từ bước 1
axios.get('/calls/{call_id}/agora-token')
  .then(res => console.log('Token:', res.data))
  .catch(err => console.error('Error:', err))
```

#### Bước 3: Kiểm tra component có render không
```javascript
// Trong Browser Console
document.querySelector('.call-overlay') // Phải có element này
```

### 6. Common Issues

#### Issue: Component không render
**Kiểm tra:**
- `showVideoCall` có được set thành `true`?
- `currentCallId` có giá trị?
- Có lỗi JavaScript nào không?

#### Issue: Không thể join channel
**Kiểm tra:**
- Token có được generate đúng không?
- App ID và Certificate có đúng không?
- Network có kết nối được đến Agora servers không?

#### Issue: Không thấy video
**Kiểm tra:**
- Camera có được bật không?
- Browser có quyền truy cập camera không?
- Có lỗi trong console không?

### 7. Debug Commands

Thêm vào Browser Console để debug:

```javascript
// Kiểm tra Agora client
window.agoraClient // Nếu có

// Kiểm tra local tracks
window.localVideoTrack // Nếu có
window.localAudioTrack // Nếu có

// Kiểm tra config
window.agoraConfig // Nếu có
```

## Liên hệ

Nếu vẫn gặp vấn đề, vui lòng cung cấp:
1. Console logs đầy đủ
2. Network requests (screenshot)
3. Error messages (nếu có)
4. Browser và version
5. Steps to reproduce

