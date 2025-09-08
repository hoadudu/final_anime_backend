# 🚀 Workflow – User Anime List (Updated)

## 1. Yêu cầu chức năng

* Mỗi user có **1 danh sách anime mặc định** (kiểu "My List").
* Trong danh sách:
  * Thêm anime (Post).
  * Gỡ anime.
  * Cập nhật trạng thái anime (Watching, Completed, On-Hold, Dropped, Plan to Watch).
  * Cho điểm / đánh giá nhanh (1-10).
  * Ghi chú cá nhân.
* Sau này:
  * VIP hoặc thành viên cấp cao → có nhiều danh sách tùy biến (ví dụ: "Top 10 mùa Xuân", "Waifu list" 🤭).
  * Public/Private setting cho từng list.

---

## 2. Database schema (cập nhật cho dự án hiện tại)

**users** (đã có)
* id, name, email, …

**anime_posts** (đã có - tương đương anime)
* id, mal_id, title, slug, type, source, episodes, status, airing, …

**user_anime_lists**
* id
* user_id (FK → users)
* name (default: "My List")
* type (enum: default, custom)
* is_default (boolean)
* visibility (enum: public, private, friends_only)
* created_at, updated_at

**user_anime_list_items**
* id
* list_id (FK → user_anime_lists)
* post_id (FK → anime_posts) // sử dụng anime_posts thay vì anime
* status (enum: watching, completed, on_hold, dropped, plan_to_watch)
* score (tinyint nullable, 1–10)
* note (text nullable, ghi chú cá nhân)
* created_at, updated_at
* UNIQUE(list_id, post_id) // một anime chỉ xuất hiện 1 lần trong 1 list

---

## 3. Workflow Backend (Laravel)

### 3.1 Tạo danh sách mặc định khi user đăng ký

* Khi user mới được tạo (`User::created` event) → backend tạo luôn `user_anime_lists` record với `is_default = true, name = "My List"`.
* Sau này nếu nâng cấp VIP → cho phép tạo thêm list.

---

### 3.2 Thêm anime vào list

**API**: `POST /api/me/anime-list/items`
Request:

```json
{
  "post_id": 123,
  "status": "watching",
  "score": 8,
  "note": "Tập đầu hay quá!"
}
```

Flow:

1. Backend tìm list mặc định (`$user->animeLists()->where('is_default', true)->first()`).
2. Nếu anime chưa có trong list → thêm mới.
3. Nếu đã có → cập nhật status/score/note.
4. Trả về JSON item với thông tin anime.

---

### 3.3 Xem danh sách anime

**API**: `GET /api/me/anime-list`

* Trả về danh sách item phân trang.
* Cho phép filter theo status (`?status=completed`).
* Sắp xếp theo ngày cập nhật mới nhất.
* Response:

```json
{
  "list_name": "My List",
  "total_items": 150,
  "stats": {
    "watching": 5,
    "completed": 120,
    "on_hold": 3,
    "dropped": 2,
    "plan_to_watch": 20
  },
  "items": [
    {
      "id": 1,
      "anime": { 
        "id": 123, 
        "title": "Fullmetal Alchemist: Brotherhood",
        "display_title": "Fullmetal Alchemist: Brotherhood",
        "mal_id": 5114,
        "type": "TV",
        "episodes": 64,
        "status": "Finished Airing",
        "images": [...],
        "slug": "fullmetal-alchemist-brotherhood"
      },
      "status": "completed",
      "score": 9,
      "note": "Masterpiece! Best anime ever.",
      "updated_at": "2025-09-08"
    }
  ]
}
```

---

### 3.4 Gỡ anime khỏi list

**API**: `DELETE /api/me/anime-list/items/{id}`

* Xóa record khỏi `user_anime_list_items`.

---

### 3.5 Cập nhật status / score / note

**API**: `PATCH /api/me/anime-list/items/{id}`
Request:

```json
{
  "status": "completed",
  "score": 10,
  "note": "Đã xem lại lần 3, vẫn tuyệt vời!"
}
```

---

### 3.6 Thống kê cá nhân

**API**: `GET /api/me/anime-list/stats`

```json
{
  "total_anime": 150,
  "total_episodes": 3500,
  "average_score": 7.8,
  "time_spent_days": 85.5,
  "status_breakdown": {
    "watching": 5,
    "completed": 120,
    "on_hold": 3,
    "dropped": 2,
    "plan_to_watch": 20
  },
  "top_genres": ["Action", "Drama", "Adventure"],
  "completed_this_year": 45
}
```

---

## 4. API Routes

```php
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    // User anime list management
    Route::get('/me/anime-list', [UserAnimeListController::class, 'index']);
    Route::get('/me/anime-list/stats', [UserAnimeListController::class, 'stats']);
    Route::post('/me/anime-list/items', [UserAnimeListController::class, 'store']);
    Route::patch('/me/anime-list/items/{item}', [UserAnimeListController::class, 'update']);
    Route::delete('/me/anime-list/items/{item}', [UserAnimeListController::class, 'destroy']);
    
    // Public profile (sau này)
    Route::get('/users/{user}/anime-list', [PublicAnimeListController::class, 'show']);
});
```

---

## 5. Quản trị với FilamentPHP

* **UserAnimeListResource**: để admin kiểm tra list user, xoá list spam.
* **UserAnimeListItemResource**: để admin check dữ liệu, thống kê top anime.
* Dashboard thống kê:
  * Top anime nhiều user xem nhất.
  * Số lượng user đang xem theo mùa.
  * Average score của từng anime.

---

## 6. Mở rộng (VIP, nâng cấp sau)

* Cho phép user có nhiều list (`user_anime_lists`).
* Mỗi list có thể public/private/friends-only.
* Thêm tính năng "chia sẻ list" (xuất link).
* Import/Export list từ MyAnimeList, AniList.
* Tính cấp độ user dựa trên số anime đã completed (gamification).
* Recommendation system dựa trên taste similarity.

---
