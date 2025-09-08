<?php

namespace App\Filament\Resources\StreamSubtitles;

use App\Filament\Resources\StreamSubtitles\Pages\CreateStreamSubtitle;
use App\Filament\Resources\StreamSubtitles\Pages\EditStreamSubtitle;
use App\Filament\Resources\StreamSubtitles\Pages\ListStreamSubtitles;
use App\Filament\Resources\StreamSubtitles\Schemas\StreamSubtitleForm;
use App\Filament\Resources\StreamSubtitles\Tables\StreamSubtitlesTable;
use App\Models\StreamSubtitle;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StreamSubtitleResource extends Resource
{
    protected static ?string $model = StreamSubtitle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;
    
    protected static string|UnitEnum|null $navigationGroup = 'Media Management';
    
    protected static ?string $navigationLabel = 'Stream Subtitles';
    
    protected static ?string $pluralLabel = 'Stream Subtitles';
    
    protected static ?string $label = 'Stream Subtitle';
    
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return StreamSubtitleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StreamSubtitlesTable::configure($table);
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
            'index' => ListStreamSubtitles::route('/'),
            'create' => CreateStreamSubtitle::route('/create'),
            'edit' => EditStreamSubtitle::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
