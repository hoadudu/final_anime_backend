<?php

namespace App\Filament\Resources\StreamSubtitles\Pages;

use App\Filament\Resources\StreamSubtitles\StreamSubtitleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStreamSubtitle extends EditRecord
{
    protected static string $resource = StreamSubtitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
