<?php

namespace App\Filament\Resources\UserAnimeListItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserAnimeListItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('list_id')
                    ->relationship('list', 'name')
                    ->required(),
                Select::make('post_id')
                    ->relationship('post', 'id')
                    ->required(),
                Select::make('status')
                    ->options([
            'watching' => 'Watching',
            'completed' => 'Completed',
            'on_hold' => 'On hold',
            'dropped' => 'Dropped',
            'plan_to_watch' => 'Plan to watch',
        ])
                    ->default('plan_to_watch')
                    ->required(),
                TextInput::make('score')
                    ->numeric(),
                Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }
}
