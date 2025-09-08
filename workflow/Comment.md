# 🚀 Workflow Backend – Hệ thống bình luận cho Anime Platform

## 1. Kiến trúc tổng thể

* **Laravel**: làm backend, xuất API JSON cho frontend.
* **Sanctum**: xác thực token (đã có sẵn cho user anime list).
* **Database**: tự tạo bảng comments và reports (không dùng package để tối ưu hơn).
* **FilamentPHP**: quản trị comments, reports, users (ẩn/xóa comment vi phạm).

---

## 2. Database schema (cập nhật cho dự án hiện tại)

**users** (đã có)
* id, name, email, password...

**anime_posts** (đã có - Post model)
* id, mal_id, title, slug, type, episodes...

**comments** (mới tạo)
* id
* user_id (FK → users)
* post_id (FK → anime_posts) // bình luận cho anime
* parent_id (FK → comments, nullable) // reply comment
* content (text)
* is_approved (boolean, default true)
* is_hidden (boolean, default false) // admin ẩn comment vi phạm
* likes_count (integer, default 0)
* dislikes_count (integer, default 0)
* created_at, updated_at

**comment_likes** (mới tạo)
* id
* user_id (FK → users)
* comment_id (FK → comments)
* type (enum: 'like', 'dislike')
* created_at, updated_at
* UNIQUE(user_id, comment_id) // một user chỉ like/dislike 1 lần

**comment_reports** (mới tạo)
* id
* user_id (FK → users) // người báo cáo
* comment_id (FK → comments)
* reason (enum: 'spam', 'inappropriate', 'harassment', 'other')
* description (text nullable)
* status (enum: 'pending', 'resolved', 'dismissed')
* resolved_by (FK → users, nullable) // admin xử lý
* resolved_at (timestamp nullable)
* created_at, updated_at

---

## 3. Luồng nghiệp vụ (Workflow Backend)

### 3.1 Xác thực
* User login → backend trả về **Sanctum token** (đã có sẵn).
* Mọi request API comment cần `Authorization: Bearer <token>`.

### 3.2 Thêm bình luận cho anime

**API**: `POST /api/anime/{post}/comments`
Request:
```json
{
  "content": "Anime này hay quá! Tập 5 cực kì hấp dẫn.",
  "parent_id": null
}
```

Flow:
1. Validate: content không rỗng, tối đa 1000 ký tự
2. Check rate limit: không quá 5 comment/phút
3. Lưu comment với user_id và post_id
4. Trả về comment với thông tin user

### 3.3 Hiển thị bình luận anime

**API**: `GET /api/anime/{post}/comments?page=1&sort=latest&parent_id=null`

Response:
```json
{
  "data": [
    {
      "id": 1,
      "content": "Anime này hay quá!",
      "user": {
        "id": 10,
        "name": "Alice",
        "avatar": "..."
      },
      "likes_count": 5,
      "dislikes_count": 1,
      "user_reaction": "like",
      "replies_count": 3,
      "created_at": "2025-09-08T10:30:00Z",
      "replies": [
        {
          "id": 2,
          "content": "Chuẩn luôn!",
          "user": { "id": 11, "name": "Bob" },
          "likes_count": 2,
          "created_at": "2025-09-08T11:00:00Z"
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 45,
    "per_page": 10
  }
}
```

### 3.4 Like / Dislike comment

**API**: `POST /api/comments/{comment}/like`
**API**: `POST /api/comments/{comment}/dislike`

Flow:
1. Check user đã react chưa
2. Nếu chưa → tạo mới, tăng counter
3. Nếu rồi và cùng type → xóa (unlike)
4. Nếu rồi nhưng khác type → update type, cập nhật counter

### 3.5 Báo cáo vi phạm

**API**: `POST /api/comments/{comment}/report`
Request:
```json
{
  "reason": "inappropriate",
  "description": "Nội dung không phù hợp với trẻ em"
}
```

### 3.6 Xóa comment của chính mình

**API**: `DELETE /api/comments/{comment}`
- Chỉ cho phép user xóa comment của chính mình
- Hoặc admin có thể xóa bất kỳ comment nào

---

## 4. API Routes (cập nhật)

```php
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    // Comments for anime
    Route::get('/anime/{post}/comments', [CommentController::class, 'index']);
    Route::post('/anime/{post}/comments', [CommentController::class, 'store']);
    
    // Comment actions
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);
    Route::post('/comments/{comment}/dislike', [CommentController::class, 'dislike']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Comment reports
    Route::post('/comments/{comment}/report', [CommentReportController::class, 'store']);
});
```

---

## 5. Quản trị với FilamentPHP

* **CommentResource**: 
  - Xem tất cả comments
  - Ẩn/hiện comment
  - Xóa comment vi phạm
  - Filter theo anime, user, trạng thái

* **CommentReportResource**:
  - Danh sách báo cáo
  - Xử lý báo cáo (ẩn comment, dismiss)
  - Thống kê số lượng báo cáo theo lý do

* **Dashboard widgets**:
  - Số comment mới hôm nay
  - Số báo cáo chưa xử lý
  - Top anime có nhiều comment nhất

---

## 6. Tối ưu và bảo mật

* **Rate limiting**: 5 comments/phút, 10 likes/phút
* **Content filtering**: Lọc từ ngữ nhạy cảm
* **Pagination**: 10-20 comments per page
* **Caching**: Cache comment count cho mỗi anime
* **Real-time**: Laravel Echo + Broadcasting cho comment mới
* **Notification**: Thông báo khi có reply hoặc like

---

## 6. Tối ưu và bảo mật

* **Rate limiting**: 5 comments/phút, 10 likes/phút
* **Content filtering**: Lọc từ ngữ nhạy cảm
* **Pagination**: 10-20 comments per page
* **Caching**: Cache comment count cho mỗi anime
* **Real-time**: Laravel Echo + Broadcasting cho comment mới
* **Notification**: Thông báo khi có reply hoặc like

---

---

### 3.5 Báo cáo vi phạm (Report)

**API**: `POST /comments/{comment}/report`

1. User gửi `{ reason }`.
2. Backend lưu vào bảng `reports`.
3. Comment giữ nguyên trạng thái, đợi admin xử lý.

---

### 3.6 Quản trị với FilamentPHP

* Dùng **Filament Resources** cho:

  * **CommentsResource**: duyệt, ẩn, xóa bình luận.
  * **ReportsResource**: danh sách report, xử lý (mark resolved / ban user / ẩn comment).
* Workflow:

  * Admin login Filament → vào “Reports” → xem lý do.
  * Nếu hợp lệ → set comment.status = hidden.

---

## 4. API Routes (ví dụ)

```php
Route::middleware('auth:sanctum')->group(function () {
    // Comments
    Route::post('/movies/{movie}/comments', [CommentController::class, 'store']);
    Route::get('/movies/{movie}/comments', [CommentController::class, 'index']);

    // Like / Dislike
    Route::post('/comments/{comment}/like', [LikeController::class, 'like']);
    Route::post('/comments/{comment}/dislike', [LikeController::class, 'dislike']);

    // Report
    Route::post('/comments/{comment}/report', [ReportController::class, 'store']);
});
```

---

## 5. Tối ưu

* **Phân trang**: `paginate(10)` để tránh query nặng.
* **Cache**: Redis cho comment “hot” (nhiều like).
* **Rate limit**: middleware `throttle:5,1` để chống spam comment.
* **Event/Broadcast**: Laravel Echo + Pusher/Socket.io → realtime comment.

---
