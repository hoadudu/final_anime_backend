<?php

namespace App\Filament\Resources\AnimeGroups\Pages;

use App\Filament\Resources\AnimeGroups\AnimeGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnimeGroups extends ListRecords
{
    protected static string $resource = AnimeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
