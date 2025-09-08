<?php

namespace App\Filament\Resources\UserAnimeLists;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\UserAnimeList;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NavigationSort;
use App\Filament\Resources\UserAnimeLists\Pages\EditUserAnimeList;
use App\Filament\Resources\UserAnimeLists\Pages\ListUserAnimeLists;
use App\Filament\Resources\UserAnimeLists\Pages\CreateUserAnimeList;
use App\Filament\Resources\UserAnimeLists\Schemas\UserAnimeListForm;
use App\Filament\Resources\UserAnimeLists\Tables\UserAnimeListsTable;

class UserAnimeListResource extends Resource
{
    protected static ?string $model = UserAnimeList::class;

    protected static ?string $navigationLabel = 'User Anime Lists';

    protected static ?int $navigationSort = NavigationSort::USER_ANIMELIST; // Adjusted for proper ordering

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserAnimeListForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAnimeListsTable::configure($table);
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
            'index' => ListUserAnimeLists::route('/'),
            'create' => CreateUserAnimeList::route('/create'),
            'edit' => EditUserAnimeList::route('/{record}/edit'),
        ];
    }
}
