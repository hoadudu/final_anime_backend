<?php

namespace App\Filament\Resources\Characters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class CharactersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->getStateUsing(function ($record) {
                        // Try to get image from different sources
                        if ($record->images) {
                            if (isset($record->images['jpg']['image_url'])) {
                                return $record->images['jpg']['image_url'];
                            } elseif (isset($record->images['webp']['image_url'])) {
                                return $record->images['webp']['image_url'];
                            }
                        }
                        return null;
                    })
                    ->circular()
                    ->defaultImageUrl('/images/default-avatar.svg')
                    ->height(40)
                    ->width(40),
                TextColumn::make('mal_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('name_kanji')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('nicknames')
                    ->label('Nicknames')
                    ->getStateUsing(function ($record) {
                        return $record->nicknames ? count($record->nicknames) . ' nicknames' : 'None';
                    }),
                TextColumn::make('about')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                //
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
