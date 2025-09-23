<?php

namespace App\Filament\Resources\AnimeCollections\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class AnimeCollectionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->unique()
                    ->required(),
                Textarea::make('description'),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('type')
                    ->options(['auto' => 'Auto', 'manual' => 'Manual'])
                    ->default('manual')
                    ->required(),
                Repeater::make('collectionPosts')
                    ->relationship() // hoặc ->relationship('animeCollectionPosts') nếu tên relation khác
                    ->schema([
                        Select::make('post_id')
                            ->relationship(
                                name: 'post',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn (Builder $query) => $query->whereNotNull('title')
                            )
                            ->searchable()
                            ->preload(), // tuỳ cần
                        Textarea::make('backdrop_image')->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpan('full')
            ]);
    }
}
