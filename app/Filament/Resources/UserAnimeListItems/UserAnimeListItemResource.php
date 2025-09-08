<?php

namespace App\Filament\Resources\UserAnimeListItems;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\UserAnimeListItem;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NavigationSort;
use App\Filament\Resources\UserAnimeListItems\Pages\EditUserAnimeListItem;
use App\Filament\Resources\UserAnimeListItems\Pages\ListUserAnimeListItems;
use App\Filament\Resources\UserAnimeListItems\Pages\CreateUserAnimeListItem;
use App\Filament\Resources\UserAnimeListItems\Schemas\UserAnimeListItemForm;
use App\Filament\Resources\UserAnimeListItems\Tables\UserAnimeListItemsTable;

class UserAnimeListItemResource extends Resource
{
    protected static ?string $model = UserAnimeListItem::class;

    protected static ?int $navigationSort = NavigationSort::USER_ANIMELIST_ITEMS; // Adjusted for proper ordering

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserAnimeListItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAnimeListItemsTable::configure($table);
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
            'index' => ListUserAnimeListItems::route('/'),
            'create' => CreateUserAnimeListItem::route('/create'),
            'edit' => EditUserAnimeListItem::route('/{record}/edit'),
        ];
    }
}
