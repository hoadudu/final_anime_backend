<?php

namespace App\Filament\Resources\AnimeGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

use Filament\Schemas\Schema;

class AnimeGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Repeater::make('groupPosts')
                    ->relationship('groupPosts')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('post_id')
                            ->relationship('post', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getDisplayTitleAttribute() ?? 'No Title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('position')
                            ->numeric(),
                        TextInput::make('note'),
                    ])
                    ->columns(3)
                    ->createItemButtonLabel('Thêm post vào nhóm'),
            ])->columns(2);
    }
}
