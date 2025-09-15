<?php

namespace App\Filament\Resources\Streams\Pages;

use Filament\Actions\DeleteAction;
use App\Services\SubtitleUploadService;
use Filament\Support\Icons\Heroicon;
use App\Filament\Actions\UploadSubtitlesAction;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Streams\StreamResource;

class EditStream extends EditRecord
{
    protected static string $resource = StreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            UploadSubtitlesAction::forPost($this->record->episode->post)
                ->label('Upload Subtitles')
                ->icon(Heroicon::OutlinedBookOpen)
                ->color('success')
                ->visible(fn() => $this->record && $this->record->episode && $this->record->episode->post),
        ];
    }

    public function getDirectoryFiles()
    {
        if (!$this->record) return [];
        
        $uploadService = app(SubtitleUploadService::class);
        return $uploadService->getDirectoryFiles($this->record->episode->post);
    }

    public function getCurrentDirectory()
    {
        if (!$this->record) return '';
        
        $uploadService = app(SubtitleUploadService::class);
        return $uploadService->getUploadDirectory($this->record->episode->post);
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}
