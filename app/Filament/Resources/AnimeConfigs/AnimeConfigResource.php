<?php

namespace App\Filament\Resources\AnimeConfigs;

use App\Filament\Resources\AnimeConfigs\Pages\CreateAnimeConfig;
use App\Filament\Resources\AnimeConfigs\Pages\EditAnimeConfig;
use App\Filament\Resources\AnimeConfigs\Pages\ListAnimeConfigs;
use App\Filament\Resources\AnimeConfigs\Schemas\AnimeConfigForm;
use App\Filament\Resources\AnimeConfigs\Tables\AnimeConfigsTable;
use App\Models\AnimeConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnimeConfigResource extends Resource
{
    protected static ?string $model = AnimeConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AnimeConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnimeConfigsTable::configure($table);
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
            'index' => ListAnimeConfigs::route('/'),
            'create' => CreateAnimeConfig::route('/create'),
            'edit' => EditAnimeConfig::route('/{record}/edit'),
        ];
    }
}
