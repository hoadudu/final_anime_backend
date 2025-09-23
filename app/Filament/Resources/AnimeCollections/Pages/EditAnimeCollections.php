<?php

namespace App\Filament\Resources\AnimeCollections\Pages;

use App\Filament\Resources\AnimeCollections\AnimeCollectionsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnimeCollections extends EditRecord
{
    protected static string $resource = AnimeCollectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
