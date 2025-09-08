<?php

namespace App\Filament\Resources\StreamSubtitles\Pages;

use App\Filament\Resources\StreamSubtitles\StreamSubtitleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStreamSubtitles extends ListRecords
{
    protected static string $resource = StreamSubtitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
