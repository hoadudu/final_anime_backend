<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mal_id')
                    ->copyable()
                    ->sortable(),
                TextColumn::make('display_title')
                    ->label('Tiêu Đề'),
                TextColumn::make('titles.title')
                    ->label('Tìm theo tiêu đề phụ')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('episodes')
                    ->label('Episodes')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->episodes()->count();
                    })
                    ->url(function ($record) {
                        return URL::route('filament.admin.resources.episodes.index', [
                            'tableFilters[post_filter][post_id]' => $record->id,
                        ]);
                    })
                    ->openUrlInNewTab(false)
                    ->color('primary')
                    ->icon('heroicon-o-play')
                    ->iconPosition('before'),
                TextColumn::make('status')
                    ->searchable(),
                IconColumn::make('airing')
                    ->boolean(),
                TextColumn::make('aired_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('aired_to')
                    ->date()
                    ->sortable(),
                TextColumn::make('duration')
                    ->searchable(),
                TextColumn::make('rating')
                    ->searchable(),
                TextColumn::make('season')
                    ->searchable(),
                TextColumn::make('broadcast')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
