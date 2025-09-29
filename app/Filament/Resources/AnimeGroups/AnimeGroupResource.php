<?php

namespace App\Filament\Resources\AnimeGroups;

use App\Filament\Resources\AnimeGroups\Pages\CreateAnimeGroup;
use App\Filament\Resources\AnimeGroups\Pages\EditAnimeGroup;
use App\Filament\Resources\AnimeGroups\Pages\ListAnimeGroups;
use App\Filament\Resources\AnimeGroups\Schemas\AnimeGroupForm;
use App\Filament\Resources\AnimeGroups\Tables\AnimeGroupsTable;
use App\Models\AnimeGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnimeGroupResource extends Resource
{
    protected static ?string $model = AnimeGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AnimeGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnimeGroupsTable::configure($table);
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
            'index' => ListAnimeGroups::route('/'),
            'create' => CreateAnimeGroup::route('/create'),
            'edit' => EditAnimeGroup::route('/{record}/edit'),
        ];
    }
}
