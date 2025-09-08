<?php

namespace App\Filament\Resources\BulkSubtitleUploadResource\Pages;

use App\Filament\Resources\BulkSubtitleUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBulkSubtitleUploads extends ListRecords
{
    protected static string $resource = BulkSubtitleUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
