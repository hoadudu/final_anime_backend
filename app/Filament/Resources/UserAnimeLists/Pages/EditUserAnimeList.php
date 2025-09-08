<?php

namespace App\Filament\Resources\UserAnimeLists\Pages;

use App\Filament\Resources\UserAnimeLists\UserAnimeListResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserAnimeList extends EditRecord
{
    protected static string $resource = UserAnimeListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
