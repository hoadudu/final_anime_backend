<?php

namespace App\Filament\Resources\Billboards;

use UnitEnum;
use BackedEnum;
use App\Models\Billboard;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NavigationSort;
use App\Filament\Resources\Billboards\Pages\EditBillboard;
use App\Filament\Resources\Billboards\Pages\ListBillboards;
use App\Filament\Resources\Billboards\Pages\CreateBillboard;
use App\Filament\Resources\Billboards\Schemas\BillboardForm;
use App\Filament\Resources\Billboards\Tables\BillboardsTable;

class BillboardResource extends Resource
{
    protected static ?string $model = Billboard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Billboards';
    protected static ?int $navigationSort = NavigationSort::BILLBOARDS;
    
    protected static string|UnitEnum|null $navigationGroup = 'Films';

    public static function form(Schema $schema): Schema
    {
        return BillboardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillboardsTable::configure($table);
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
            'index' => ListBillboards::route('/'),
            'create' => CreateBillboard::route('/create'),
            'edit' => EditBillboard::route('/{record}/edit'),
        ];
    }
}
