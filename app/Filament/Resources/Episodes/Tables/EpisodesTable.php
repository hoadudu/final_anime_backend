<?php

namespace App\Filament\Resources\Episodes\Tables;

use Filament\Tables\Table;
use Tables\Filters\Filter;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;


use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Filters\Filter as BaseFilter;


class EpisodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.id')
                    ->formatStateUsing(fn ($state, $record) => $record->post?->display_title ?: "Post #{$state}")
                    ->label('Anime Title'),
                TextColumn::make('episode_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('absolute_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('thumbnail')
                    ->searchable(),
                TextColumn::make('release_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('type'),
                TextColumn::make('group')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sort_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TrashedFilter::make(),
                BaseFilter::make('post_filter')
                    ->form([
                        \Filament\Forms\Components\Select::make('post_id')
                            ->label('Anime Post')
                            ->placeholder('Select an anime...')
                            ->options(function () {
                                return \App\Models\Post::with('titles')
                                    ->get()
                                    ->mapWithKeys(function ($post) {
                                        $title = $post->display_title;
                                        return [$post->id => $title ?: "Post #{$post->id}"];
                                    })
                                    ->sort();
                            })
                            ->searchable()
                            ->preload()
                            ->default(function () {
                                // Handle URL parameter: ?tableFilters[post_filter][post_id]=3
                                $filters = request()->get('tableFilters', []);
                                return $filters['post_filter']['post_id'] ?? null;
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['post_id'] ?? null,
                                fn (Builder $query, $postId): Builder => $query->where('anime_episodes.post_id', $postId),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['post_id'] ?? null) {
                            $post = \App\Models\Post::with('titles')->find($data['post_id']);
                            if ($post) {
                                $indicators['post_id'] = 'Anime: ' . ($post->display_title ?: "Post #{$post->id}");
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
                    BulkAction::make('update_absolute_number')
                        ->label('Update Absolute Numbers')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->form([
                            TextInput::make('increment_value')
                                ->label('Add to Absolute Number')
                                ->helperText('Enter a number to add to the current absolute_number of selected episodes. Use negative numbers to subtract.')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->rules(['integer']),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $incrementValue = (int) $data['increment_value'];
                            $updatedCount = 0;
                            
                            foreach ($records as $episode) {
                                $currentAbsoluteNumber = $episode->absolute_number ?? 0;
                                $newAbsoluteNumber = $currentAbsoluteNumber + $incrementValue;
                                
                                // Ensure absolute number doesn't go below 1
                                if ($newAbsoluteNumber < 1) {
                                    $newAbsoluteNumber = 1;
                                }
                                
                                $episode->update([
                                    'absolute_number' => $newAbsoluteNumber
                                ]);
                                
                                $updatedCount++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Absolute Numbers Updated')
                                ->body("Successfully updated {$updatedCount} episodes. Added {$incrementValue} to each absolute number.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Update Absolute Numbers')
                        ->modalDescription('This will add the specified number to the absolute_number of all selected episodes.')
                        ->modalSubmitActionLabel('Update Episodes'),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
