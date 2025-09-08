<?php

namespace App\Filament\Resources;

use App\Models\Post;
use App\Models\Stream;
use App\Models\StreamSubtitle;
use App\Services\SubtitleUploadService;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BulkSubtitleUploadResource\Pages;

class BulkSubtitleUploadResource extends Resource
{
    protected static ?string $model = Post::class; // Using Post as base model for convenience

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected static ?string $navigationLabel = 'Bulk Subtitle Upload';

    protected static ?string $pluralLabel = 'Bulk Subtitle Upload';

    protected static ?string $label = 'Bulk Upload';

    protected static string | \UnitEnum | null $navigationGroup = 'Media Management';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Select Anime & Episodes')
                    ->description('Choose the anime and episodes for subtitle upload')
                    ->schema([
                        Select::make('post_id')
                            ->label('Select Anime')
                            ->relationship('post', 'title')
                            ->searchable(['title'])
                            ->preload()
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['episodeList.streams.subtitles']))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Anime Title')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('episodes_count')
                    ->label('Episodes')
                    ->counts('episodeList'),
                    
                Tables\Columns\TextColumn::make('streams_count')
                    ->label('Streams')
                    ->getStateUsing(function ($record) {
                        return $record->episodeList->sum(function ($episode) {
                            return $episode->streams->count();
                        });
                    }),
                    
                Tables\Columns\TextColumn::make('subtitles_count')
                    ->label('Subtitles')
                    ->getStateUsing(function ($record) {
                        return $record->episodeList->sum(function ($episode) {
                            return $episode->streams->sum(function ($stream) {
                                return $stream->subtitles->count();
                            });
                        });
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('bulk_upload')
                    ->label('Bulk Upload Subtitles')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->form([
                        FileUpload::make('subtitle_file')
                            ->label('Subtitle File')
                            ->acceptedFileTypes(['.srt', '.vtt', '.ass', '.ssa', '.txt'])
                            ->required(),
                            
                        CheckboxList::make('stream_ids')
                            ->label('Select Streams')
                            ->options(function ($record) {
                                return Stream::whereHas('episode', function ($query) use ($record) {
                                    $query->where('post_id', $record->id);
                                })
                                ->with('episode')
                                ->get()
                                ->mapWithKeys(function ($stream) {
                                    return [
                                        $stream->id => "Ep.{$stream->episode->episode_number} - {$stream->server_name} ({$stream->quality})"
                                    ];
                                });
                            })
                            ->required(),
                            
                        Select::make('language')
                            ->options([
                                'vi' => 'Vietnamese',
                                'en' => 'English',
                                'ja' => 'Japanese',
                                'ko' => 'Korean',
                            ])
                            ->required(),
                            
                        Select::make('source')
                            ->options([
                                'manual' => 'Manual',
                                'official' => 'Official',
                                'community' => 'Community',
                            ])
                            ->default('manual'),
                            
                        Toggle::make('is_default')
                            ->label('Set as Default'),
                    ])
                    ->action(function (Post $record, array $data) {
                        $uploadService = app(SubtitleUploadService::class);
                        
                        $results = $uploadService->uploadAndAssignToStreams(
                            $data['subtitle_file'],
                            $record,
                            $data['stream_ids'],
                            $data['language'],
                            [
                                'source' => $data['source'] ?? 'manual',
                                'is_default' => $data['is_default'] ?? false,
                                'is_active' => true,
                                'sort_order' => 0,
                            ]
                        );
                        
                        Notification::make()
                            ->title('Bulk Upload Complete')
                            ->body('Successfully uploaded subtitles to ' . count($results) . ' streams')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBulkSubtitleUploads::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Post::whereHas('episodeList', function ($query) {
            $query->whereHas('streams');
        })->count();
    }
}
