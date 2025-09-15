<?php

namespace App\Filament\Resources\Streams\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\URL;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class StreamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('episode.episode_number')
                    ->label('Episode #')
                    ->formatStateUsing(fn ($state, $record) => 
                        "Ep. {$state} - " . ($record->episode?->post?->title ?? 'Unknown Anime')
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subtitles_count')
                    ->label('Subtitles')
                    ->getStateUsing(function ($record) {
                        return $record->subtitles()->count();
                    })
                    ->badge()
                    ->color('success')
                    ->url(function ($record) {
                        if (!$record) return null;
                        
                        return URL::route('filament.admin.resources.stream-subtitles.index', [
                            'tableFilters[stream_id]' => $record->id,
                        ]);
                    })
                    ->openUrlInNewTab(false)
                    ->tooltip('Click to view subtitles for this stream')
                      ->icon(Heroicon::CursorArrowRays),
                TextColumn::make('server_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Stream Info')
                    ->getStateUsing(fn ($record) => $record->display_name),
                TextColumn::make('stream_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'direct' => 'success',
                        'hls', 'm3u8' => 'info',
                        'embed' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('url')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->copyable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('episode_filter')
                    ->form([
                        Select::make('episode_id')
                            ->label('Episode')
                            ->placeholder('Select an episode...')
                            ->options(function () {
                                return \App\Models\Episode::with('post')
                                    ->get()
                                    ->mapWithKeys(function ($episode) {
                                        $postTitle = $episode->post?->title ?? 'Unknown Anime';
                                        $episodeLabel = "Ep. {$episode->episode_number} - {$postTitle}";
                                        return [$episode->id => $episodeLabel];
                                    })
                                    ->sort();
                            })
                            ->searchable()
                            ->preload()
                            ->default(function () {
                                // Handle URL parameter: ?tableFilters[episode_filter][episode_id]=3
                                $filters = request()->get('tableFilters', []);
                                return $filters['episode_filter']['episode_id'] ?? null;
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['episode_id'] ?? null,
                                fn (Builder $query, $episodeId): Builder => $query->where('anime_episode_streams.episode_id', $episodeId),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['episode_id'] ?? null) {
                            $episode = \App\Models\Episode::with('post')->find($data['episode_id']);
                            if ($episode) {
                                $postTitle = $episode->post?->title ?? 'Unknown Anime';
                                $indicators['episode_id'] = "Episode: Ep. {$episode->episode_number} - {$postTitle}";
                            }
                        }
                        
                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
