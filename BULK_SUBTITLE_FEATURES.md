# Bulk Subtitle Upload - Multi-Language Support

## Tính năng mới đã được thêm:

### 1. **Upload nhiều file phụ đề**
- Sử dụng Filament Repeater component
- Có thể thêm/xóa nhiều file trong một form
- Mỗi file có thể có ngôn ngữ và cài đặt riêng

### 2. **Hỗ trợ nhiều ngôn ngữ**
- Vietnamese (vi)
- English (en)  
- Japanese (ja)
- Korean (ko)
- Chinese (zh)
- Thai (th)
- French (fr)
- German (de)
- Spanish (es)
- Portuguese (pt)
- Russian (ru)
- Arabic (ar)

### 3. **Tự động phát hiện từ tên file**
- Định dạng file: .srt, .vtt, .ass, .ssa, .txt
- Ngôn ngữ từ tên file (ví dụ: episode1.vi.srt)
- Tự động điền form dựa trên phân tích tên file

### 4. **Cài đặt linh hoạt cho từng file**
- Đánh dấu là phụ đề mặc định cho ngôn ngữ
- Thứ tự hiển thị tự động
- Trạng thái hoạt động
- Nguồn phụ đề (manual, official, community, auto)

### 5. **Giao diện cải tiến**
- Header với icon và mô tả
- Hướng dẫn sử dụng chi tiết
- Tips và best practices
- Thông báo kết quả chi tiết
- Xử lý lỗi từng file riêng biệt

### 6. **Xử lý thông minh**
- Upload batch với error handling
- Hiển thị tiến trình từng file
- Thống kê kết quả upload
- Rollback nếu cần thiết

## Cách sử dụng:

1. **Truy cập:** `/admin/bulk-subtitle-upload`
2. **Chọn anime** và các episode/stream cần thêm phụ đề
3. **Thêm nhiều file phụ đề:**
   - Click "Add Another Subtitle File"
   - Upload file và chọn ngôn ngữ
   - Đánh dấu default nếu cần
4. **Cấu hình chung:** Source type, trạng thái active
5. **Upload:** Hệ thống sẽ xử lý tất cả file và báo cáo kết quả

## Ví dụ tên file được hỗ trợ:
- `episode1.vi.srt` → Tiếng Việt, SRT format
- `ep01.en.vtt` → Tiếng Anh, VTT format  
- `01.ja.ass` → Tiếng Nhật, ASS format
- `episode_1_korean.ko.srt` → Tiếng Hàn, SRT format

## API Support:
Service `SubtitleUploadService` đã được tối ưu để xử lý:
- Multiple file uploads trong một request
- Language detection và validation
- Batch processing với error handling
- File management và cleanup
