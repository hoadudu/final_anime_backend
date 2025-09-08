<?php

namespace App\Filament\Resources\Producers;

use App\Filament\Resources\Producers\Pages\CreateProducer;
use App\Filament\Resources\Producers\Pages\EditProducer;
use App\Filament\Resources\Producers\Pages\ListProducers;
use App\Filament\Resources\Producers\Schemas\ProducerForm;
use App\Filament\Resources\Producers\Tables\ProducersTable;
use App\Models\Producer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\NavigationSort;

class ProducerResource extends Resource
{
    protected static ?string $model = Producer::class;

    protected static ?int $navigationSort = NavigationSort::PRODUCER;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Producers';

    public static function form(Schema $schema): Schema
    {
        return ProducerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProducersTable::configure($table);
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
            'index' => ListProducers::route('/'),
            'create' => CreateProducer::route('/create'),
            'edit' => EditProducer::route('/{record}/edit'),
        ];
    }
}
