<?php

namespace App\Filament\Resources\UserAnimeLists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserAnimeListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->default('My List'),
                Select::make('type')
                    ->options(['default' => 'Default', 'custom' => 'Custom'])
                    ->default('default')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                Select::make('visibility')
                    ->options(['public' => 'Public', 'private' => 'Private', 'friends_only' => 'Friends only'])
                    ->default('private')
                    ->required(),
            ]);
    }
}
