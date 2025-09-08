<?php

namespace App\Filament\Resources\Genres\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GenresForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mal_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('description'),
                Select::make('type')
                    ->options([
            'genres' => 'Genres',
            'explicit_genres' => 'Explicit genres',
            'themes' => 'Themes',
            'demographics' => 'Demographics',
        ])
                    ->default('genres')
                    ->required(),
            ]);
    }
}
