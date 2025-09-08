<?php

namespace App\Filament\Resources\StreamSubtitles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Stream;
use App\Models\Post;

class StreamSubtitleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subtitle Information')
                    ->description('Basic subtitle information and settings')
                    ->schema([
                        Group::make()
                            ->schema([
                                Select::make('stream_id')
                                    ->label('Stream')
                                    ->relationship('stream', 'id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getDisplayNameAttribute() . ' (ID: ' . $record->id . ')')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Select::make('language')
                                    ->label('Language Code')
                                    ->options([
                                        'vi' => 'vi (Vietnamese)',
                                        'en' => 'en (English)',
                                        'ja' => 'ja (Japanese)',
                                        'ko' => 'ko (Korean)',
                                        'zh' => 'zh (Chinese)',
                                        'th' => 'th (Thai)',
                                        'fr' => 'fr (French)',
                                        'de' => 'de (German)',
                                        'es' => 'es (Spanish)',
                                        'pt' => 'pt (Portuguese)',
                                        'ru' => 'ru (Russian)',
                                        'ar' => 'ar (Arabic)',
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $names = [
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
                                            ];
                                            $set('language_name', $names[$state] ?? ucfirst($state));
                                        }
                                    }),
                            ])
                            ->columns(2),
                        
                        TextInput::make('language_name')
                            ->label('Language Display Name')
                            ->required()
                            ->placeholder('e.g., Vietnamese, English'),
                        
                        Group::make()
                            ->schema([
                                Select::make('type')
                                    ->label('Subtitle Format')
                                    ->options([
                                        'srt' => 'SRT (SubRip)',
                                        'vtt' => 'VTT (WebVTT)',
                                        'ass' => 'ASS (Advanced SubStation)',
                                        'ssa' => 'SSA (SubStation Alpha)',
                                        'txt' => 'TXT (Plain Text)'
                                    ])
                                    ->default('srt')
                                    ->required(),
                                
                                Select::make('source')
                                    ->label('Source Type')
                                    ->options([
                                        'manual' => 'Manual Upload',
                                        'auto' => 'Auto Generated',
                                        'community' => 'Community Contributed',
                                        'official' => 'Official Subtitle'
                                    ])
                                    ->default('manual')
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
                
                Section::make('File Upload')
                    ->description('Upload subtitle file or provide external URL')
                    ->schema([
                        FileUpload::make('subtitle_file')
                            ->label('Upload Subtitle File')
                            ->acceptedFileTypes(['application/x-subrip', 'text/vtt', 'text/x-ssa', '.srt', '.vtt', '.ass', '.ssa', '.txt'])
                            ->disk('local')
                            ->directory(function (Get $get) {
                                $streamId = $get('stream_id');
                                if ($streamId) {
                                    $stream = Stream::find($streamId);
                                    $episode = $stream?->episode;
                                    $post = $episode?->post;
                                    if ($post) {
                                        return "subtitles/{$post->id}_{$post->slug}";
                                    }
                                }
                                return 'subtitles/temp';
                            })
                            ->storeFileNamesIn('original_filename')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    // Auto-fill URL when file is uploaded
                                    $set('url', $state);
                                    
                                    // Auto-detect type from extension
                                    $extension = pathinfo($state, PATHINFO_EXTENSION);
                                    $set('type', strtolower($extension));
                                }
                            })
                            ->helperText('Supported formats: SRT, VTT, ASS, SSA, TXT'),
                    ]),
                
                Section::make('URL & Settings')
                    ->description('Subtitle file location and display settings')
                    ->schema([
                        Textarea::make('url')
                            ->label('Subtitle File URL')
                            ->placeholder('https://cdn.example.com/subtitles/episode-1-vi.srt')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Group::make()
                            ->schema([
                                Toggle::make('is_default')
                                    ->label('Default Subtitle')
                                    ->helperText('Set as the default subtitle for this stream'),
                                
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
                            ->columns(3),
                    ]),
                
                Section::make('Metadata')
                    ->description('Additional subtitle metadata (optional)')
                    ->schema([
                        KeyValue::make('meta')
                            ->label('Metadata')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->helperText('Additional properties like encoding, offset, fps, etc.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
