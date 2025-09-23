<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;

use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Services\SubtitleUploadService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Actions\UploadSubtitlesAction;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mal_id')
                    ->copyable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Tiêu Đề')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('titles.title')
                    ->label('Tìm theo tiêu đề phụ')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(),
                TextColumn::make('type'),
                    
                TextColumn::make('source'),
                    
                TextColumn::make('episodes')
                    ->label('Episodes')
                    ->numeric()
                    
                    ->getStateUsing(function ($record) {
                        return $record->episodeList()->count();
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
                TextColumn::make('subtitle_files_count')
                    ->label('Subtitle Files')
                    ->getStateUsing(function ($record) {
                        $subtitleService = app(SubtitleUploadService::class);
                        return $subtitleService->getSubtitleFilesCount($record);
                    })
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(false)
                    ->action(UploadSubtitlesAction::make()),
                TextColumn::make('status'),
                    
                IconColumn::make('airing')
                    ->boolean(),
                TextColumn::make('aired_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('aired_to')
                    ->date()
                    ->sortable(),
                TextColumn::make('duration'),
                    
                TextColumn::make('rating'),
                    
                TextColumn::make('season'),
                    
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
