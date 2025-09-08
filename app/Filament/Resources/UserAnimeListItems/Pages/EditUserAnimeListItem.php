<?php

namespace App\Filament\Resources\UserAnimeListItems\Pages;

use App\Filament\Resources\UserAnimeListItems\UserAnimeListItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserAnimeListItem extends EditRecord
{
    protected static string $resource = UserAnimeListItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
