<?php

namespace App\Filament\Resources\AnimeConfigs\Pages;

use App\Filament\Resources\AnimeConfigs\AnimeConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnimeConfig extends EditRecord
{
    protected static string $resource = AnimeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
