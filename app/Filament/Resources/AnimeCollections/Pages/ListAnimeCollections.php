<?php

namespace App\Filament\Resources\AnimeCollections\Pages;

use App\Filament\Resources\AnimeCollections\AnimeCollectionsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnimeCollections extends ListRecords
{
    protected static string $resource = AnimeCollectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
