# Subtitles Management System

## 📋 Overview
Hệ thống quản lý phụ đề cho video streams, hỗ trợ nhiều ngôn ngữ và format, với kiến trúc scalable cho tương lai.

## 🎯 Goals
- Quản lý phụ đề cho từng stream của episode
- Hỗ trợ nhiều ngôn ngữ và format phụ đề
- Linh hoạt với external storage (CDN, S3, etc.)
- Admin interface để quản lý
- API endpoints cho video player

## 🏗️ Architecture

### Database Schema
```sql
CREATE TABLE `stream_subtitles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` bigint unsigned NOT NULL,
  `language` varchar(10) NOT NULL COMMENT 'Language code: vi, en, ja, etc.',
  `language_name` varchar(50) NOT NULL COMMENT 'Display name: Vietnamese, English, etc.',
  `type` enum('srt','vtt','ass','ssa','txt') NOT NULL DEFAULT 'srt',
  `url` text NOT NULL COMMENT 'URL to subtitle file (CDN, S3, external)',
  `source` enum('manual','auto','community','official') NOT NULL DEFAULT 'manual',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `meta` json DEFAULT NULL COMMENT 'Metadata: encoding, offset, fps',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_stream_lang_type` (`stream_id`,`language`,`type`),
  KEY `idx_stream_language` (`stream_id`,`language`),
  KEY `idx_stream_default` (`stream_id`,`is_default`),
  CONSTRAINT `stream_subtitles_stream_id_foreign` FOREIGN KEY (`stream_id`) REFERENCES `anime_episode_streams` (`id`) ON DELETE CASCADE
);
```

### Model Relationships
- `StreamSubtitle` belongs to `Stream`
- `Stream` has many `StreamSubtitle`

## 🔧 Implementation Steps

### Step 1: Create Migration
- [x] Generate migration file
- [x] Define table structure with proper indexes
- [x] Add foreign key constraints

### Step 2: Create Model
- [x] StreamSubtitle model with relationships
- [x] Query scopes for filtering
- [x] Helper methods for metadata
- [x] Accessors for display formatting

### Step 3: Update Stream Model
- [x] Add subtitles relationship
- [x] Helper methods for subtitle management
- [x] Default subtitle logic

### Step 4: Create Filament Resource
- [x] Admin interface for CRUD operations
- [x] Form with language/type selection
- [x] Table with filtering and sorting
- [x] Bulk actions for management

### Step 5: API Endpoints
- [x] Include subtitles in stream response
- [x] Subtitle language preferences
- [x] Default subtitle logic

### Step 6: Testing & Validation
- [ ] Test subtitle CRUD operations
- [ ] Test API responses
- [ ] Validate foreign key constraints
- [ ] Test default subtitle logic

## 📝 Features

### Core Features
- **Multi-language Support**: vi, en, ja, ko, zh, th
- **Multiple Formats**: SRT, WebVTT, ASS/SSA, Plain Text
- **Source Tracking**: Manual, Auto-generated, Community, Official
- **Default Language**: Auto-select based on user preference
- **Active Status**: Enable/disable subtitles
- **Ordering**: Custom sort order for display

### Advanced Features
- **Metadata Storage**: Encoding, time offset, FPS
- **External Storage**: Support for CDN, S3, external URLs
- **Admin Management**: Full CRUD via Filament
- **API Integration**: Ready for video players

## 🔄 Data Flow

### Adding Subtitles
1. Admin uploads/adds subtitle via Filament
2. System validates language/type uniqueness
3. URL stored pointing to external file
4. Metadata extracted and stored
5. Default flags updated if necessary

### Serving Subtitles
1. Frontend requests stream data
2. API returns stream + available subtitles
3. Video player loads subtitle from URL
4. Default subtitle auto-selected based on preference

### Managing Defaults
1. One default per language per stream
2. Setting new default clears previous
3. Fallback logic for missing preferences

## 🎮 Frontend Integration

### Video Player Integration
```javascript
// Example subtitle data from API
{
  "stream": {
    "id": 1,
    "url": "https://stream.example.com/video.m3u8",
    "subtitles": [
      {
        "language": "vi",
        "language_name": "Vietnamese", 
        "url": "https://cdn.example.com/subtitles/ep1_vi.srt",
        "type": "srt",
        "is_default": true
      },
      {
        "language": "en",
        "language_name": "English",
        "url": "https://cdn.example.com/subtitles/ep1_en.srt", 
        "type": "srt",
        "is_default": false
      }
    ]
  }
}
```

### Player Implementation
- Auto-load default subtitle
- Subtitle selection menu
- Support for multiple formats
- Time offset adjustment

## 🔒 Security Considerations

### URL Validation
- Validate subtitle URLs before storage
- Check file accessibility
- Prevent malicious URLs

### Access Control
- Admin-only subtitle management
- API rate limiting for subtitle requests
- CORS policy for subtitle files

## 📈 Scalability

### Current Design Benefits
- External file storage (no DB bloat)
- CDN-ready architecture
- Microservice-friendly
- Easy migration to separate subtitle service

### Future Enhancements
- Auto-translation integration
- Community subtitle contributions
- Quality scoring system
- Subtitle synchronization tools
- Batch upload/processing

## 🧪 Testing Strategy

### Unit Tests
- Model validation and relationships
- Helper method functionality
- Scope query accuracy

### Integration Tests
- Filament resource operations
- API endpoint responses
- Foreign key constraints

### Manual Testing
- Admin interface usability
- Subtitle loading in video player
- Default subtitle logic
- Language switching functionality

## 📊 Monitoring & Analytics

### Metrics to Track
- Subtitle usage by language
- Default subtitle effectiveness
- API response times
- Failed subtitle loads

### Logging
- Subtitle CRUD operations
- API access patterns
- Error tracking for broken URLs

## 🚀 Deployment Notes

### Environment Setup
- Ensure CDN/S3 connectivity
- Configure CORS for subtitle files
- Set up proper file permissions

### Database Migration
- Run migration with proper indexes
- Validate foreign key relationships
- Test with sample data

### Post-Deployment
- Verify admin interface functionality
- Test API endpoints
- Validate subtitle loading
- Monitor error logs

---

## ✅ Implementation Checklist

- [ ] Create migration file
- [ ] Implement StreamSubtitle model
- [ ] Update Stream model with relationships
- [ ] Create Filament resource
- [ ] Update API endpoints
- [ ] Add validation rules
- [ ] Write unit tests
- [ ] Manual testing
- [ ] Documentation update
- [ ] Deploy to staging
- [ ] Production deployment
