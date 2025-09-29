<?php

namespace App\Filament\Resources\AnimeConfigs\Pages;

use App\Filament\Resources\AnimeConfigs\AnimeConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnimeConfigs extends ListRecords
{
    protected static string $resource = AnimeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
