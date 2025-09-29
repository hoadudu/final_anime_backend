<?php

namespace App\Filament\Resources\Genres;

use App\Filament\Resources\Genres\Pages\CreateGenres;
use App\Filament\Resources\Genres\Pages\EditGenres;
use App\Filament\Resources\Genres\Pages\ListGenres;
use App\Filament\Resources\Genres\Schemas\GenresForm;
use App\Filament\Resources\Genres\Tables\GenresTable;
use App\Models\Genre;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NavigationSort;

class GenresResource extends Resource
{
    protected static ?string $model = Genre::class;
    protected static ?int $navigationSort = NavigationSort::GENRES;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GenresForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GenresTable::configure($table);
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
            'index' => ListGenres::route('/'),
            'create' => CreateGenres::route('/create'),
            'edit' => EditGenres::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
