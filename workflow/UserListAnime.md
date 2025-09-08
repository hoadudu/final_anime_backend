# 🚀 Workflow – User Anime List

## 1. Yêu cầu chức năng

* Mỗi user có **1 danh sách anime mặc định** (kiểu “My List”).
* Trong danh sách:

  * Thêm anime.
  * Gỡ anime.
  * Cập nhật trạng thái anime (Watching, Completed, On-Hold, Dropped, Plan to Watch).
  * Cho điểm / đánh giá nhanh.
* Sau này:

  * VIP hoặc thành viên cấp cao → có nhiều danh sách tùy biến (ví dụ: “Top 10 mùa Xuân”, “Waifu list” 🤭).
  * Public/Private setting cho từng list.

---

## 2. Database schema

**users**

* id, name, email, …

**anime**

* id, title, description, studio, year, …

**user\_anime\_lists**

* id
* user\_id
* name (default: “My List”)
* type (default/custom sau này)
* is\_default (boolean)
* visibility (public/private/friends-only sau này)
* created\_at, updated\_at

**user\_anime\_list\_items**

* id
* list\_id (FK → user\_anime\_lists)
* anime\_id (FK → anime)
* status (enum: watching, completed, on\_hold, dropped, plan\_to\_watch)
* score (tinyint, optional 1–10)
* note (text optional, ví dụ comment riêng)
* created\_at, updated\_at

---

## 3. Workflow Backend (Laravel)

### 3.1 Tạo danh sách mặc định khi user đăng ký

* Khi user mới được tạo (`User::created` event) → backend tạo luôn `user_anime_lists` record với `is_default = true, name = "My List"`.
* Sau này nếu nâng cấp VIP → cho phép tạo thêm list.

---

### 3.2 Thêm anime vào list

**API**: `POST /me/anime-list/items`
Request:

```json
{
  "anime_id": 123,
  "status": "watching",
  "score": 8
}
```

Flow:

1. Backend tìm list mặc định (`$user->animeLists()->where('is_default', true)->first()`).
2. Nếu anime chưa có trong list → thêm mới.
3. Nếu đã có → cập nhật status/score.
4. Trả về JSON item.

---

### 3.3 Xem danh sách anime

**API**: `GET /me/anime-list`

* Trả về danh sách item phân trang.
* Cho phép filter theo status (`?status=completed`).
* Response:

```json
{
  "list_name": "My List",
  "items": [
    {
      "anime": { "id": 123, "title": "Naruto" },
      "status": "completed",
      "score": 9,
      "updated_at": "2025-09-08"
    }
  ]
}
```

---

### 3.4 Gỡ anime khỏi list

**API**: `DELETE /me/anime-list/items/{id}`

* Xóa record khỏi `user_anime_list_items`.

---

### 3.5 Cập nhật status / score

**API**: `PATCH /me/anime-list/items/{id}`
Request:

```json
{
  "status": "completed",
  "score": 10
}
```

---

### 3.6 Mở rộng (VIP, nâng cấp sau)

* Cho phép user có nhiều list (`user_anime_lists`).
* Mỗi list có thể public/private.
* Thêm tính năng “chia sẻ list” (xuất link).
* Tính cấp độ user dựa trên số anime đã completed (gamification).

---

## 4. API Routes gợi ý

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me/anime-list', [AnimeListController::class, 'index']);
    Route::post('/me/anime-list/items', [AnimeListController::class, 'store']);
    Route::patch('/me/anime-list/items/{item}', [AnimeListController::class, 'update']);
    Route::delete('/me/anime-list/items/{item}', [AnimeListController::class, 'destroy']);
});
```

---

## 5. Quản trị với FilamentPHP

* **UserAnimeListsResource**: để admin kiểm tra list user, xoá list spam.
* **UserAnimeListItemsResource**: để admin check dữ liệu nếu cần (hiếm khi phải vào).
* Sau này có thể thêm dashboard thống kê:

  * Top anime nhiều user xem nhất.
  * Số lượng user đang xem theo mùa.

---
