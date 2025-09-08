<?php

namespace App\Filament\Resources\UserAnimeLists\Pages;

use App\Filament\Resources\UserAnimeLists\UserAnimeListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAnimeLists extends ListRecords
{
    protected static string $resource = UserAnimeListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
