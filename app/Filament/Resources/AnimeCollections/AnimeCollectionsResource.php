<?php

namespace App\Filament\Resources\AnimeCollections;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\AnimeCollection;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NavigationSort;
use App\Filament\Resources\AnimeCollections\Pages\EditAnimeCollections;
use App\Filament\Resources\AnimeCollections\Pages\ListAnimeCollections;
use App\Filament\Resources\AnimeCollections\Pages\CreateAnimeCollections;
use App\Filament\Resources\AnimeCollections\Schemas\AnimeCollectionsForm;
use App\Filament\Resources\AnimeCollections\Tables\AnimeCollectionsTable;

class AnimeCollectionsResource extends Resource
{
    protected static ?string $model = AnimeCollection::class;

    protected static ?int $navigationSort = NavigationSort::POSTS;

    protected static ?string $navigationLabel = 'Collections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Films';

    public static function form(Schema $schema): Schema
    {
        return AnimeCollectionsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnimeCollectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnimeCollections::route('/'),
            'create' => CreateAnimeCollections::route('/create'),
            'edit' => EditAnimeCollections::route('/{record}/edit'),
        ];
    }
}
