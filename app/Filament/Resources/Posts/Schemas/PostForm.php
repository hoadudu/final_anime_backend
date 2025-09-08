<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Genres;
use App\Models\Producer;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Post Information')
                    ->tabs([
                        Tabs\Tab::make('Thông tin cơ bản')
                            ->schema([
                                TextInput::make('mal_id')
                                    ->numeric(),
                                TextInput::make('slug')
                                    ->required()
                                    ->suffixAction(
                                        Action::make('GenerateSlug')
                                            ->label('Generate Slug')
                                            ->icon('heroicon-m-arrow-path')
                                            ->action(function (Get $get, Set $set) {
                                                $titles = $get('titles') ?? [];
                                                $primaryTitle = collect($titles)->firstWhere('is_primary', true)['title'] ?? null;
                                                if ($primaryTitle) {
                                                    $set('slug', \Illuminate\Support\Str::slug($primaryTitle));
                                                }
                                            })
                                    )
                                    ->unique(Post::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('type'),
                                TextInput::make('source'),
                                TextInput::make('episodes')
                                    ->numeric(),
                                TextInput::make('status'),
                                Toggle::make('airing'),
                                    
                                DatePicker::make('aired_from'),
                                DatePicker::make('aired_to'),
                                TextInput::make('duration'),
                                TextInput::make('rating'),
                                Textarea::make('synopsis')
                                    ->columnSpanFull(),
                                Textarea::make('background')
                                    ->columnSpanFull(),
                                TextInput::make('season'),
                                DateTimePicker::make('broadcast'),
                                Repeater::make('external')
                                    ->label('External Links (JSON)')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Name')
                                            
                                            ->maxLength(100),
                                        TextInput::make('url')
                                            ->label('URL')
                                            
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed()
                                    ->reorderable()
                                    ->addActionLabel('➕ Add External Link')
                                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                    ->columns(2),
                                Toggle::make('approved')
                                    ->required(),
                            ])->columns(2),

                        Tabs\Tab::make('Tiêu đề')
                            ->schema([
                                Repeater::make('titles')
                                    ->label('Các tên của phim')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Tiêu đề')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('type')
                                            ->label('Loại tiêu đề')
                                            ->options([
                                                'Default' => 'Default',
                                                'Synonym' => 'Synonym',
                                                'Official' => 'Official',
                                                'Alternative' => 'Alternative',
                                            ])
                                            ->default('Official')
                                            ->required(),
                                        TextInput::make('language')
                                            ->label('Ngôn ngữ')
                                            ->maxLength(2)
                                            ->placeholder('VD: en, ja, vi'),
                                        Toggle::make('is_primary')
                                            ->label('Là tiêu đề chính?')
                                            ->default(false),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(4)
                                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),
                            ]),

                        Tabs\Tab::make('Hình ảnh')
                            ->schema([
                                Repeater::make('images')
                                    ->label('Hình ảnh')
                                    ->relationship()
                                    ->schema([
                                        // Hàng 1: URL ảnh (full width)
                                        TextInput::make('image_url')
                                            ->label('🔗 URL ảnh')
                                            ->url()
                                            ->placeholder('https://example.com/image.jpg')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->columnSpanFull()
                                            ->suffixIcon('heroicon-m-link'),

                                        // Hàng 2: Preview ảnh và thông tin cơ bản
                                        ViewField::make('image_preview')
                                            ->label('🖼️ Xem trước')
                                            ->view('filament.forms.image-preview')
                                            ->viewData(fn(Get $get) => [
                                                'imageUrl' => $get('image_url')
                                            ])
                                            ->columnSpan(2),

                                        // Thông tin ảnh
                                        Select::make('image_type')
                                            ->label('📂 Loại ảnh')
                                            ->options([
                                                'poster' => '🎬 Poster',
                                                'cover' => '📕 Cover',
                                                'banner' => '🏷️ Banner',
                                                'thumbnail' => '🖼️ Thumbnail',
                                                'gallery' => '🖼️ Gallery',
                                                'screenshot' => '📸 Screenshot',
                                                'other' => '📄 Other',
                                            ])
                                            ->default('poster')
                                            ->required()
                                            ->native(false)
                                            ->columnSpan(1),

                                        TextInput::make('language')
                                            ->label('🌐 Ngôn ngữ')
                                            ->maxLength(2)
                                            ->default('en')
                                            ->placeholder('en, ja, vi')
                                            ->prefixIcon('heroicon-m-language')
                                            ->columnSpan(1),

                                        Toggle::make('is_primary')
                                            ->label('⭐ Ảnh chính')
                                            ->default(false)
                                            ->inline(false)
                                            ->helperText('Chỉ một ảnh có thể là ảnh chính')
                                            ->columnSpan(2),

                                        // Hàng 3: Mô tả ảnh (full width)
                                        TextInput::make('alt_text')
                                            ->label('📝 Mô tả ảnh')
                                            ->placeholder('Mô tả ngắn gọn về ảnh (SEO)')
                                            ->columnSpanFull()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-m-pencil-square'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed()
                                    ->reorderable()
                                    ->addActionLabel('➕ Thêm ảnh mới')
                                    //->deleteActionLabel('🗑️ Xóa ảnh')
                                    // ->reorderActionLabel('🔄 Sắp xếp lại')
                                    ->itemLabel(function (array $state): ?string {
                                        $url = $state['image_url'] ?? '';
                                        $type = $state['image_type'] ?? 'other';
                                        $primary = $state['is_primary'] ?? false;

                                        $typeIcons = [
                                            'poster' => '🎬',
                                            'cover' => '📕',
                                            'banner' => '🏷️',
                                            'thumbnail' => '🖼️',
                                            'gallery' => '🖼️',
                                            'screenshot' => '📸',
                                            'other' => '📄',
                                        ];

                                        $icon = $typeIcons[$type] ?? '🖼️';
                                        $label = $icon . ' ' . ucfirst($type);

                                        if ($primary) {
                                            $label = '⭐ ' . $label;
                                        }

                                        if ($url) {
                                            $filename = basename(parse_url($url, PHP_URL_PATH));
                                            return $label . ' • ' . substr($filename, 0, 25) . (strlen($filename) > 25 ? '...' : '');
                                        }

                                        return $label . ' • Chưa có URL';
                                    })
                                    ->columns(4)
                                    ->defaultItems(0)
                                    ->cloneable(),
                            ]),
                        Tabs\Tab::make('Videos')
                            ->schema([
                                Repeater::make('videos')
                                    ->label('Video')
                                    ->relationship()
                                    ->schema([
                                        // Hàng 1: Tiêu đề video (full width)
                                        TextInput::make('title')
                                            ->label('🎬 Tiêu đề video')
                                            ->placeholder('VD: Official Trailer, Opening Theme, Episode 1...')
                                            ->required()
                                            ->columnSpanFull()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-m-film'),

                                        // Hàng 2: URL video (full width)
                                        TextInput::make('url')
                                            ->label('🔗 URL video')
                                            ->url()
                                            ->placeholder('https://www.youtube.com/watch?v=... hoặc URL video khác')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->columnSpanFull()
                                            ->suffixIcon('heroicon-m-link')
                                            ->helperText('Hỗ trợ YouTube, Vimeo và các URL video trực tiếp'),

                                        // Hàng 3: Preview video và thông tin
                                        ViewField::make('video_preview')
                                            ->label('🎬 Xem trước video')
                                            ->view('filament.forms.video-preview')
                                            ->viewData(fn(Get $get) => [
                                                'videoUrl' => $get('url')
                                            ])
                                            ->columnSpan(3),

                                        // Loại video
                                        Select::make('video_type')
                                            ->label('📂 Loại video')
                                            ->options([
                                                'promo' => '🎥 Promo/Trailer',
                                                'music_videos' => '🎵 Music Video',
                                                'episodes' => '📺 Episode',
                                                'other' => '📄 Khác',
                                            ])
                                            ->default('promo')
                                            ->required()
                                            ->native(false)
                                            ->columnSpan(1),

                                        // Hàng 4: Meta data (JSON) - có thể collapse
                                        Textarea::make('meta')
                                            ->label('🔧 Metadata (JSON)')
                                            ->placeholder('{"youtube_id": "...", "embed_url": "...", "images": {...}}')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->helperText('Dữ liệu bổ sung từ API (tự động điền khi import)')
                                            ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                                            ->dehydrateStateUsing(fn($state) => $state ? json_decode($state, true) : null),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed()
                                    ->reorderable()
                                    ->addActionLabel('➕ Thêm video mới')
                                    // ->deleteActionLabel('🗑️ Xóa video')
                                    // ->reorderActionLabel('🔄 Sắp xếp lại')
                                    ->itemLabel(function (array $state): ?string {
                                        $title = $state['title'] ?? '';
                                        $url = $state['url'] ?? '';
                                        $type = $state['video_type'] ?? 'other';

                                        $typeIcons = [
                                            'promo' => '🎥',
                                            'music_videos' => '🎵',
                                            'episodes' => '📺',
                                            'other' => '📄',
                                        ];

                                        $icon = $typeIcons[$type] ?? '🎬';
                                        $label = $icon . ' ' . ucfirst(str_replace('_', ' ', $type));

                                        if ($title) {
                                            return $label . ' • ' . substr($title, 0, 30) . (strlen($title) > 30 ? '...' : '');
                                        }

                                        if ($url) {
                                            $domain = parse_url($url, PHP_URL_HOST);
                                            return $label . ' • ' . ($domain ?: 'Video mới');
                                        }

                                        return $label . ' • Chưa có thông tin';
                                    })
                                    ->columns(4)
                                    ->defaultItems(0)
                                    ->cloneable(),
                            ]),

                        Tabs\Tab::make('Genres & Themes')
                            ->schema([
                                ViewField::make('genres_heading')
                                    ->label('Phân loại theo chủ đề')
                                    ->view('filament.forms.components.section-heading')
                                    ->viewData(['heading' => '🎬 Phân loại phim anime theo chủ đề'])
                                    ->columnSpanFull(),

                                // Genres CheckboxList
                                CheckboxList::make('genre_ids')
                                    ->label('🎭 Thể loại chung')
                                    ->options(fn() => Genres::where('type', 'genres')->pluck('name', 'id')->toArray())
                                    ->descriptions(fn() => Genres::where('type', 'genres')->pluck('description', 'id')->toArray())
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (CheckboxList $component, $state, $record) {
                                        if ($record) {
                                            $genreIds = $record->morphables()
                                                ->where('morphable_type', Genres::class)
                                                ->whereHas('morphable', fn($q) => $q->where('type', 'genres'))
                                                ->pluck('morphable_id')
                                                ->toArray();
                                            $component->state($genreIds);
                                        }
                                    })
                                    ->dehydrated(false),

                                // Explicit Genres CheckboxList
                                CheckboxList::make('explicit_genre_ids')
                                    ->label('🔞 Nội dung người lớn')
                                    ->options(fn() => Genres::where('type', 'explicit_genres')->pluck('name', 'id')->toArray())
                                    ->descriptions(fn() => Genres::where('type', 'explicit_genres')->pluck('description', 'id')->toArray())
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (CheckboxList $component, $state, $record) {
                                        if ($record) {
                                            $genreIds = $record->morphables()
                                                ->where('morphable_type', Genres::class)
                                                ->whereHas('morphable', fn($q) => $q->where('type', 'explicit_genres'))
                                                ->pluck('morphable_id')
                                                ->toArray();
                                            $component->state($genreIds);
                                        }
                                    })
                                    ->dehydrated(false),

                                // Themes CheckboxList
                                CheckboxList::make('theme_ids')
                                    ->label('🎨 Chủ đề')
                                    ->options(fn() => Genres::where('type', 'themes')->pluck('name', 'id')->toArray())
                                    ->descriptions(fn() => Genres::where('type', 'themes')->pluck('description', 'id')->toArray())
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (CheckboxList $component, $state, $record) {
                                        if ($record) {
                                            $genreIds = $record->morphables()
                                                ->where('morphable_type', Genres::class)
                                                ->whereHas('morphable', fn($q) => $q->where('type', 'themes'))
                                                ->pluck('morphable_id')
                                                ->toArray();
                                            $component->state($genreIds);
                                        }
                                    })
                                    ->dehydrated(false),

                                // Demographics CheckboxList
                                CheckboxList::make('demographic_ids')
                                    ->label('👥 Đối tượng')
                                    ->options(fn() => Genres::where('type', 'demographics')->pluck('name', 'id')->toArray())
                                    ->descriptions(fn() => Genres::where('type', 'demographics')->pluck('description', 'id')->toArray())
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (CheckboxList $component, $state, $record) {
                                        if ($record) {
                                            $genreIds = $record->morphables()
                                                ->where('morphable_type', Genres::class)
                                                ->whereHas('morphable', fn($q) => $q->where('type', 'demographics'))
                                                ->pluck('morphable_id')
                                                ->toArray();
                                            $component->state($genreIds);
                                        }
                                    })
                                    ->dehydrated(false),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Producers & Companies')
                            ->schema([
                                ViewField::make('producers_heading')
                                    ->label('Công ty sản xuất')
                                    ->view('filament.forms.components.section-heading')
                                    ->viewData(['heading' => '🏢 Công ty sản xuất, cấp phép và studio'])
                                    ->columnSpanFull(),

                                // Producers Select
                                Select::make('producer_ids')
                                    ->label('🎬 Nhà sản xuất')
                                    ->multiple()
                                    ->options(fn() => Producer::pluck('titles', 'id')->map(function ($titles, $id) {
                                        $primaryTitle = collect($titles)->firstWhere('type', 'Default')['title'] ?? 'Unknown Producer';
                                        return $primaryTitle;
                                    })->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (Select $component, $state, $record) {
                                        if ($record) {
                                            $producerIds = $record->postProducers()
                                                ->where('type', 'producer')
                                                ->pluck('producer_id')
                                                ->toArray();
                                            $component->state($producerIds);
                                        }
                                    })
                                    ->dehydrated(false),

                                // Licensors Select
                                Select::make('licensor_ids')
                                    ->label('📋 Công ty cấp phép')
                                    ->multiple()
                                    ->options(fn() => Producer::pluck('titles', 'id')->map(function ($titles, $id) {
                                        $primaryTitle = collect($titles)->firstWhere('type', 'Default')['title'] ?? 'Unknown Licensor';
                                        return $primaryTitle;
                                    })->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (Select $component, $state, $record) {
                                        if ($record) {
                                            $licensorIds = $record->postProducers()
                                                ->where('type', 'licensor')
                                                ->pluck('producer_id')
                                                ->toArray();
                                            $component->state($licensorIds);
                                        }
                                    })
                                    ->dehydrated(false),

                                // Studios Select
                                Select::make('studio_ids')
                                    ->label('🎨 Studio sản xuất')
                                    ->multiple()
                                    ->options(fn() => Producer::pluck('titles', 'id')->map(function ($titles, $id) {
                                        $primaryTitle = collect($titles)->firstWhere('type', 'Default')['title'] ?? 'Unknown Studio';
                                        return $primaryTitle;
                                    })->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function (Select $component, $state, $record) {
                                        if ($record) {
                                            $studioIds = $record->postProducers()
                                                ->where('type', 'studio')
                                                ->pluck('producer_id')
                                                ->toArray();
                                            $component->state($studioIds);
                                        }
                                    })
                                    ->dehydrated(false),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
