<?php

namespace App\Filament\Resources\StreamSubtitles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

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
            ->filters([
                SelectFilter::make('language')
                    ->label('Language')
                    ->options([
                        'vi' => 'Vietnamese',
                        'en' => 'English',
                        'ja' => 'Japanese',
                        'ko' => 'Korean',
                        'zh' => 'Chinese',
                        'th' => 'Thai',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('type')
                    ->label('Format')
                    ->options([
                        'srt' => 'SRT',
                        'vtt' => 'VTT',
                        'ass' => 'ASS',
                        'ssa' => 'SSA',
                        'txt' => 'TXT',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'manual' => 'Manual',
                        'auto' => 'Auto Generated',
                        'community' => 'Community',
                        'official' => 'Official',
                    ])
                    ->multiple(),
                    
                TernaryFilter::make('is_default')
                    ->label('Is Default'),
                    
                TernaryFilter::make('is_active')
                    ->label('Is Active'),
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
