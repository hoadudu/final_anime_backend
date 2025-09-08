<?php

namespace App\Filament\Resources\Episodes;

use BackedEnum;
use App\Models\Episode;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NavigationSort;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Episodes\Schemas\EpisodeForm;
use App\Filament\Resources\Episodes\Tables\EpisodesTable;

class EpisodeResource extends Resource
{
    protected static ?string $model = Episode::class;


    protected static ?int $navigationSort = NavigationSort::EPISODES;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EpisodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EpisodesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('post.titles');
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
            'index' => ListEpisodes::route('/'),
            'create' => CreateEpisode::route('/create'),
            'edit' => EditEpisode::route('/{record}/edit'),
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
