# Subtitle Management System

Hệ thống quản lý subtitle cho Posts được thiết kế để có thể tái sử dụng và linh hoạt.

## Cấu trúc

### 1. UploadSubtitlesAction (`app/Filament/Actions/UploadSubtitlesAction.php`)
- Action class có thể tái sử dụng cho Filament
- Hỗ trợ upload multiple files với tên gốc được bảo toàn
- Có thể sử dụng trong Tables, Forms, hoặc bất kỳ đâu trong Filament

### 2. PostSubtitleManagerService (`app/Services/PostSubtitleManagerService.php`)
- Service class chứa tất cả logic quản lý subtitle
- Có thể sử dụng ở bất kỳ đâu trong ứng dụng (Controllers, Jobs, Commands, etc.)
- Cung cấp các method tiện ích

### 3. SubtitleManagementController (`app/Http/Controllers/SubtitleManagementController.php`)
- Ví dụ về cách sử dụng service trong controller
- Cung cấp API endpoints cho subtitle management

## Cách sử dụng

### 1. Trong Filament Tables/Forms
```php
use App\Filament\Actions\UploadSubtitlesAction;

// Trong table column
->action(UploadSubtitlesAction::make())

// Hoặc với custom configuration
->action(UploadSubtitlesAction::forPost($post))
```

### 2. Trong Controllers/Services
```php
use App\Services\PostSubtitleManagerService;

$subtitleManager = app(PostSubtitleManagerService::class);

// Upload files
$count = $subtitleManager->uploadSubtitles($post, $files);

// Get files count
$count = $subtitleManager->getSubtitleFilesCount($post);

// Get all files
$files = $subtitleManager->getSubtitleFiles($post);

// Delete a file
$success = $subtitleManager->deleteSubtitleFile($post, 'filename.srt');

// Bulk upload
$results = $subtitleManager->bulkUploadSubtitles([
    'post_id_1' => [$file1, $file2],
    'post_id_2' => [$file3, $file4],
]);
```

### 3. Trong Livewire Components
```php
use App\Services\PostSubtitleManagerService;

class PostDetailComponent extends Component
{
    public $post;
    public $subtitleCount;
    
    public function mount(Post $post)
    {
        $this->post = $post;
        $subtitleManager = app(PostSubtitleManagerService::class);
        $this->subtitleCount = $subtitleManager->getSubtitleFilesCount($post);
    }
}
```

### 4. Trong Blade Templates
```php
@php
    $subtitleManager = app(App\Services\PostSubtitleManagerService::class);
    $subtitleFiles = $subtitleManager->getSubtitleFiles($post);
@endphp

@foreach($subtitleFiles as $file)
    <div>{{ $file['name'] }} ({{ $file['size'] }})</div>
@endforeach
```

### 5. Programmatic Usage (Jobs, Commands, etc.)
```php
use App\Services\PostSubtitleManagerService;
use App\Models\Post;

// Trong một Job hoặc Command
public function handle()
{
    $post = Post::find(1);
    $subtitleManager = app(PostSubtitleManagerService::class);
    
    // Upload từ file paths
    $files = [
        storage_path('app/subtitles/episode1.srt'),
        storage_path('app/subtitles/episode2.srt'),
    ];
    
    $count = $subtitleManager->uploadSubtitles($post, $files);
    $this->info("Uploaded {$count} files");
}
```

## API Endpoints (nếu sử dụng SubtitleManagementController)

```
POST /posts/{post}/subtitles/upload    - Upload subtitles
GET  /posts/{post}/subtitles           - Get subtitle files
DELETE /posts/{post}/subtitles         - Delete a subtitle file
POST /subtitles/bulk-upload            - Bulk upload for multiple posts
```

## Features

- ✅ Tái sử dụng được ở nhiều nơi
- ✅ Bảo toàn tên file gốc
- ✅ Auto-create subtitle directory
- ✅ Validation file types
- ✅ Unique filename handling
- ✅ Progress notifications
- ✅ Error handling
- ✅ Bulk operations
- ✅ Clean API

## File Structure

```
app/
├── Filament/
│   └── Actions/
│       └── UploadSubtitlesAction.php
├── Services/
│   ├── SubtitleUploadService.php (existing)
│   └── PostSubtitleManagerService.php (new)
└── Http/
    └── Controllers/
        └── SubtitleManagementController.php (example)
```
