<?php

namespace App\Filament\Resources\Producers\Pages;

use App\Filament\Resources\Producers\ProducerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProducer extends EditRecord
{
    protected static string $resource = ProducerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
