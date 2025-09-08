<?php

namespace App\Filament\Resources\Streams;

use BackedEnum;
use App\Models\Stream;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NavigationSort;
use App\Filament\Resources\Streams\Pages\EditStream;
use App\Filament\Resources\Streams\Pages\ListStreams;
use App\Filament\Resources\Streams\Pages\CreateStream;
use App\Filament\Resources\Streams\Schemas\StreamForm;
use App\Filament\Resources\Streams\Tables\StreamsTable;
use Illuminate\Database\Eloquent\Builder;

class StreamResource extends Resource
{
    protected static ?string $model = Stream::class;

    protected static ?string $navigationLabel = 'Streams';

    protected static ?int $navigationSort = NavigationSort::STREAMS;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StreamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StreamsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('episode.post.titles');
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
            'index' => ListStreams::route('/'),
            'create' => CreateStream::route('/create'),
            'edit' => EditStream::route('/{record}/edit'),
        ];
    }
}
