<?php

namespace App\Filament\Resources\Producers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class ProducerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mal_id')
                    ->required()
                    ->numeric()
                    ->label('MAL ID'),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Slug'),
                TextInput::make('established')
                    ->label('Established Date')
                    ->placeholder('YYYY-MM-DD'),

                Repeater::make('titles')
                    ->label('Producer Titles')
                    ->schema([
                        TextInput::make('type')
                            ->label('Title Type')
                            ->default('Default')
                            ->required(),
                        TextInput::make('title')
                            ->label('Title')
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add Title')
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->columnSpanFull(),

                TextInput::make('images.jpg.image_url')
                    ->label('Image URL')
                    ->url()
                    ->placeholder('https://example.com/image.jpg')
                    ->columnSpanFull(),

                Textarea::make('about')
                    ->label('About')
                    ->columnSpanFull()
                    ->rows(6),
            ]);
    }
}
