<?php

namespace App\Filament\Resources\StreamSubtitles\Tables;

use App\Models\Stream;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;

class StreamSubtitlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stream.display_name')
                    ->label('Stream')
                    ->description(fn ($record) => $record->stream?->url ? 'ID: ' . $record->stream_id : 'No stream')
                    ->searchable(['stream.server_name', 'stream.quality'])
                    ->sortable(),
                    
                BadgeColumn::make('language')
                    ->label('Language')
                    ->formatStateUsing(fn ($state, $record) => strtoupper($state) . ' - ' . $record->language_name)
                    ->colors([
                        'primary' => 'vi',
                        'success' => 'en',
                        'warning' => 'ja',
                        'danger' => fn ($state) => !in_array($state, ['vi', 'en', 'ja']),
                    ]),
                    
                BadgeColumn::make('type')
                    ->label('Format')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->colors([
                        'primary' => 'srt',
                        'success' => 'vtt',
                        'warning' => ['ass', 'ssa'],
                        'secondary' => 'txt',
                    ]),
                    
                BadgeColumn::make('source')
                    ->label('Source')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'success' => 'official',
                        'primary' => 'manual',
                        'warning' => 'community',
                        'secondary' => 'auto',
                    ]),
                    
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->sortable(),
                    
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                    
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->url)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // select filter for stream_id with default from URL parameter
            // stream()->  episode()->post()->title
            ->filters([
    SelectFilter::make('stream_id')
        ->label('Stream')
        ->default(function () {
            $filters = request()->get('tableFilters', []);
            return $filters['stream_id']['value'] ?? null; // chú ý: trong URL phải là [value]
        })
        ->options(function () {
            return Stream::with('episode.post')
                ->get()
                ->mapWithKeys(function ($stream) {
                    $episodeTitle = $stream->episode?->number ?? 'Unknown Episode';
                    $postTitle    = $stream->episode?->post?->title ?? 'Unknown Anime';

                    $label = "{$stream->display_name} ({$stream->server_name}) - {$episodeTitle} / {$postTitle}";

                    return [$stream->id => $label];
                })
                ->sortBy(fn ($label) => $label) // sort theo label, giữ nguyên key
                ->toArray();
        })
        ->searchable(),
])

            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->striped();
    }
}
