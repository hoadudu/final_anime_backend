<?php

namespace App\Filament\Pages;

use App\Models\Post;
use App\Models\Stream;
use App\Services\SubtitleUploadService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class BulkSubtitleUpload extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected string $view = 'filament.pages.bulk-subtitle-upload';

    protected static ?string $navigationLabel = 'Bulk Subtitle Upload';

    protected static string | \UnitEnum | null $navigationGroup = 'Media Management';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Select Anime & Episodes')
                    ->description('Choose the anime and episodes for subtitle upload')
                    ->schema([
                        Select::make('post_id')
                            ->label('Select Anime')
                            ->options(Post::whereHas('episodeList', function ($query) {
                                $query->whereHas('streams');
                            })->pluck('title', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('stream_ids', null)),

                        CheckboxList::make('stream_ids')
                            ->label('Select Streams')
                            ->options(function (Get $get) {
                                $postId = $get('post_id');
                                if (!$postId) return [];

                                return Stream::whereHas('episode', function ($query) use ($postId) {
                                    $query->where('post_id', $postId);
                                })
                                ->with(['episode'])
                                ->get()
                                ->mapWithKeys(function ($stream) {
                                    $episode = $stream->episode;
                                    return [
                                        $stream->id => "Episode {$episode->episode_number} - {$stream->server_name} ({$stream->quality})"
                                    ];
                                });
                            })
                            ->required()
                            ->columns(2),
                    ]),

                Section::make('Upload Subtitle File')
                    ->description('Upload the subtitle file to be assigned to selected streams')
                    ->schema([
                        FileUpload::make('subtitle_file')
                            ->label('Subtitle File')
                            ->acceptedFileTypes(['.srt', '.vtt', '.ass', '.ssa', '.txt'])
                            ->disk('local')
                            ->directory('temp/bulk-upload')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    // Auto-detect language and type from filename
                                    $filename = basename($state);
                                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                    $set('type', strtolower($extension));
                                    
                                    // Try to detect language from filename
                                    if (preg_match('/[._-](vi|en|ja|ko|zh|th)[._-]/i', $filename, $matches)) {
                                        $set('language', strtolower($matches[1]));
                                    }
                                }
                            }),

                        Select::make('language')
                            ->label('Subtitle Language')
                            ->options([
                                'vi' => 'Vietnamese',
                                'en' => 'English',
                                'ja' => 'Japanese',
                                'ko' => 'Korean',
                                'zh' => 'Chinese',
                                'th' => 'Thai',
                                'fr' => 'French',
                                'de' => 'German',
                                'es' => 'Spanish',
                                'pt' => 'Portuguese',
                                'ru' => 'Russian',
                                'ar' => 'Arabic',
                            ])
                            ->required()
                            ->searchable(),

                        Select::make('type')
                            ->label('Subtitle Format')
                            ->options([
                                'srt' => 'SRT (SubRip)',
                                'vtt' => 'VTT (WebVTT)',
                                'ass' => 'ASS (Advanced SubStation)',
                                'ssa' => 'SSA (SubStation Alpha)',
                                'txt' => 'TXT (Plain Text)',
                            ])
                            ->required(),
                    ]),

                Section::make('Subtitle Settings')
                    ->description('Configure subtitle properties')
                    ->schema([
                        Select::make('source')
                            ->label('Source Type')
                            ->options([
                                'manual' => 'Manual Upload',
                                'auto' => 'Auto Generated',
                                'community' => 'Community Contributed',
                                'official' => 'Official Subtitle',
                            ])
                            ->default('manual')
                            ->required(),

                        Toggle::make('is_default')
                            ->label('Set as Default Subtitle')
                            ->helperText('This subtitle will be the default for all selected streams'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Whether this subtitle is available for use'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order (lower numbers first)'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();
        
        $uploadService = app(SubtitleUploadService::class);
        $post = Post::find($data['post_id']);
        
        if (!$post) {
            Notification::make()
                ->title('Error')
                ->body('Selected anime not found')
                ->danger()
                ->send();
            return;
        }

        try {
            $results = $uploadService->uploadAndAssignToStreams(
                $data['subtitle_file'],
                $post,
                $data['stream_ids'],
                $data['language'],
                [
                    'source' => $data['source'] ?? 'manual',
                    'is_default' => $data['is_default'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                    'sort_order' => $data['sort_order'] ?? 0,
                ]
            );

            Notification::make()
                ->title('Bulk Upload Successful!')
                ->body('Successfully uploaded subtitles to ' . count($results) . ' streams')
                ->success()
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Upload Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
