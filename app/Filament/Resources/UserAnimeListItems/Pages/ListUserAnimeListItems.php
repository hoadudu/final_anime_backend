<?php

namespace App\Filament\Resources\UserAnimeListItems\Pages;

use App\Filament\Resources\UserAnimeListItems\UserAnimeListItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAnimeListItems extends ListRecords
{
    protected static string $resource = UserAnimeListItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
