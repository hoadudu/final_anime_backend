<?php

namespace App\Filament\Resources\AnimeGroups\Pages;

use App\Filament\Resources\AnimeGroups\AnimeGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnimeGroup extends EditRecord
{
    protected static string $resource = AnimeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
