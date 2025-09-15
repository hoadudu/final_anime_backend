<?php

namespace App\Filament\Resources\Streams\Schemas;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

use App\Services\SubtitleUploadService;
use App\Models\Episode;
use App\Helpers\LanguageCodeHelper;

use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Wizard\Step;
use App\Filament\Actions\UploadSubtitlesAction;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Support\View\Components\ButtonComponent;
use Filament\Forms\Components\Actions\Action as FormAction;


class StreamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('episode_id')
                    ->relationship('episode', 'id')
                    ->required(),
                TextInput::make('server_name'),
                TextInput::make('url'),
                TextInput::make('meta'),
                Select::make('stream_type')
                    ->options([
                        'direct' => 'Direct',
                        'embed' => 'Embed',
                        'hls' => 'Hls',
                        'm3u8' => 'M3u8',
                        'dash' => 'Dash',
                        'other' => 'Other',
                    ])
                    ->default('direct')
                    ->required(),
                Select::make('quality')
                    ->options([
                        '360p' => '360p',
                        '480p' => '480p',
                        '720p' => '720p',
                        '1080p' => '1080p',
                        '4k' => '4k',
                        'auto' => 'Auto',
                    ])
                    ->default('auto')
                    ->required(),
                Select::make('language')
                    ->options(['sub' => 'Sub', 'dub' => 'Dub', 'raw' => 'Raw'])
                    ->default('sub')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                               

                Section::make('Subtitle Management')
                    ->description('Manage subtitles for this stream')
                    ->schema([
                        


                        Repeater::make('subtitles')
                            ->relationship('subtitles')
                            ->schema([
                                Select::make('language')
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
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('type')
                                    ->options([
                                        'srt' => 'SRT',
                                        'vtt' => 'VTT',
                                        'ass' => 'ASS',
                                        'ssa' => 'SSA',
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                
                                Select::make('list_file')
                                    ->label('Danh sách file')
                                    ->options(function ($record, callable $get) {
                                        // Try to get post from record first (for existing records)
                                        if ($record && $record->stream && $record->stream->episode) {
                                            $post = $record->stream->episode->post;
                                        } else {
                                            // For new records, get episode_id from parent form
                                            $episodeId = $get('../../episode_id');
                                            if ($episodeId) {
                                                $episode = Episode::find($episodeId);
                                                $post = $episode?->post;
                                            }
                                        }

                                        if (!$post || !$post->subtitle_directory) {
                                            return [];
                                        }

                                        $files = Storage::disk('public')->files($post->subtitle_directory);

                                        return collect($files)->mapWithKeys(function ($file) {
                                            return [$file => basename($file)];
                                        })->toArray();
                                    })
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            // Set URL
                                            $set('url', $state);
                                            
                                            // Auto-detect and set language
                                            $filename = basename($state);
                                            $detectedLang = LanguageCodeHelper::detectFromFilename($filename);
                                            if ($detectedLang) {
                                                $set('language', $detectedLang);
                                            }
                                            
                                            // Auto-detect and set type from file extension
                                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                            if (in_array($extension, ['srt', 'vtt', 'ass', 'ssa'])) {
                                                $set('type', $extension);
                                            }
                                        }
                                    }),

                                TextInput::make('url')
                                    ->label('File Path Or Url')
                                    ->placeholder('Nhập URL hoặc chọn từ list bên trên...')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state && !str_starts_with($state, 'http')) {
                                            // Only auto-detect for local files, not external URLs
                                            $filename = basename($state);
                                            $detectedLang = LanguageCodeHelper::detectFromFilename($filename);
                                            if ($detectedLang && !$get('language')) {
                                                // Only set if language is not already set
                                                $set('language', $detectedLang);
                                            }
                                            
                                            // Auto-detect and set type from file extension
                                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                            if (in_array($extension, ['srt', 'vtt', 'ass', 'ssa']) && !$get('type')) {
                                                // Only set if type is not already set
                                                $set('type', $extension);
                                            }
                                        }
                                    })
                                    ->columnSpan(2),


                                Toggle::make('is_default')
                                    ->label('Default')
                                    ->columnSpan(1),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->columnSpan(1),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->label('Subtitle Records')
                            ->collapsible()
                            ->collapsed()
                            ->addActionLabel('Add Subtitle Record')
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->collapsible()
                    ->collapsed(fn($record) => $record === null)
                    ->visible(fn($record) => $record !== null),
            ]);
    }
}
