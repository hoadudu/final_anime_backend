<?php

namespace App\Filament\Resources\Episodes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class EpisodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('post_id')
                    ->relationship('post', 'id')
                    ->required()
                    ->searchable()                    
                    ->preload(),
                Repeater::make('titles')
                    ->label('Episode Titles')
                    ->schema([
                        Select::make('language')
                            ->options([
                                'en' => 'English',
                                'ja' => 'Japanese',
                                'romanji' => 'Romanji',
                                'vi' => 'Vietnamese',
                                'ko' => 'Korean',
                                'zh' => 'Chinese',
                            ])
                            ->default('en')
                            ->required(),
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->addActionLabel('Add Title')
                    ->itemLabel(function (array $state): string {
                        if (isset($state['title']) && !empty($state['title'])) {
                            return $state['title'];
                        }
                        if (isset($state['language']) && !empty($state['language'])) {
                            return "New {$state['language']} title";
                        }
                        return 'New title';
                    })
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(),

                TextInput::make('episode_number')
                    ->numeric()
                    ->required(),
                TextInput::make('absolute_number')
                    ->numeric(),
                TextInput::make('thumbnail'),
                DatePicker::make('release_date'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(['regular' => 'Regular', 'filler' => 'Filler', 'recap' => 'Recap', 'special' => 'Special'])
                    ->default('regular')
                    ->required(),
                TextInput::make('group')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('sort_number')
                    ->numeric(),
            ]);
    }
}
