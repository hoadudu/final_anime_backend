Rõ rồi 👍. Mình sẽ viết lại **workflow xây dựng hệ thống bình luận cho web anime**, lần này đặc thù là backend **Laravel** + phần quản trị **FilamentPHP** (bạn đã có sẵn).

---

# 🚀 Workflow Backend – Hệ thống bình luận với Laravel

## 1. Kiến trúc tổng thể

* **Laravel**: làm backend, xuất API JSON cho frontend (React/Vue/Nuxt/Next...).
* **Sanctum**: xác thực token (login/register cho user).
* **Packages chính**:

  * `laravelista/comments` → comment & reply.
  * `rtconner/likeable` → like/dislike cho comment.
  * Tự tạo `reports` table → quản lý báo cáo vi phạm.
* **FilamentPHP**: quản trị comments, reports, users (ẩn/xóa comment vi phạm).

---

## 2. Database schema

Laravel packages sẽ tạo sẵn migrations, chỉ bổ sung thêm `reports`.

**users**

* id, name, email, password, role...

**movies**

* id, title, description, release\_date...

**comments** (từ laravelista)

* id, commentable\_type, commentable\_id, user\_id, parent\_id, content, approved, created\_at...

**likeables** (từ rtconner/likeable)

* id, likeable\_type, likeable\_id, user\_id...

**reports**

* id, user\_id, comment\_id, reason, status (pending/resolved), created\_at...

---

## 3. Luồng nghiệp vụ (Workflow)

### 3.1 Xác thực

* User login → backend trả về **Sanctum token**.
* Mọi request API liên quan đến comment cần `Authorization: Bearer <token>`.

---

### 3.2 Thêm bình luận

**API**: `POST /movies/{movie}/comments`

1. Frontend gửi `{ content, parent_id? }`.
2. Backend:

   * Xác thực user.
   * Validate nội dung (không rỗng, tối đa ký tự, lọc từ cấm).
   * Dùng `$movie->comment($content, $user)` để lưu.
3. Trả về JSON: comment vừa thêm.

---

### 3.3 Hiển thị bình luận

**API**: `GET /movies/{movie}/comments?page=1&sort=latest`

* Backend query: `$movie->comments()->with('user')->paginate(10)`.
* Có thể eager load `children` để hiển thị dạng cây.
* Trả về JSON:

```json
[
  {
    "id": 1,
    "user": { "id": 10, "name": "Alice" },
    "content": "Anime hay quá!",
    "likes": 5,
    "replies": [
      { "id": 2, "user": { "id": 11, "name": "Bob" }, "content": "Chuẩn!", "likes": 2 }
    ]
  }
]
```

---

### 3.4 Like / Dislike

**API**: `POST /comments/{comment}/like`

* Backend: `$comment->like($user->id)`
* Nếu đã like thì toggle (unlike).

**API**: `POST /comments/{comment}/dislike`

* Tương tự, dùng method `dislike()`.

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
