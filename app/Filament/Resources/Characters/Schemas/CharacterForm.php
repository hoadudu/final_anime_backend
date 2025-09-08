<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mal_id')
                    ->required()
                    ->numeric()
                    ->label('MAL ID'),
                TextInput::make('name')
                    ->required()
                    ->label('Character Name'),
                TextInput::make('name_kanji')
                    ->label('Name (Kanji)'),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Slug'),

                // ViewField::make('avatar_preview')
                //     ->label('Avatar Preview')
                //     ->view('filament.forms.components.character-avatar')
                //     ->columnSpan(2)
                //     ->reactive(),

                TextInput::make('images.jpg.image_url')
                    ->label('JPG Image URL')
                    ->url()
                    ->placeholder('https://example.com/image.jpg')
                    ->columnSpan(2)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Trigger re-render of avatar preview when JPG URL changes
                        $set('avatar_preview', $state);
                    }),

                TextInput::make('images.webp.image_url')
                    ->label('WebP Image URL')
                    ->url()
                    ->placeholder('https://example.com/image.webp')
                    ->columnSpan(2)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Trigger re-render of avatar preview when WebP URL changes
                        $set('avatar_preview', $state);
                    }),
                TextInput::make('images.webp.small_image_url')
                    ->label('Small WebP Image URL')
                    ->url()
                    ->placeholder('https://example.com/image-small.webp')
                    ->columnSpan(2),

                Repeater::make('nicknames')
                    ->label('Character Nicknames')
                    ->schema([
                        TextInput::make('nickname')
                            ->label('Nickname')
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Add Nickname')
                    ->itemLabel(fn (array $state): ?string => $state['nickname'] ?? null)
                    ->columnSpanFull(),

                Textarea::make('about')
                    ->label('About')
                    ->columnSpanFull()
                    ->rows(8),
            ]);
    }
}
