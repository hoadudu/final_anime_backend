<?php

namespace App\Filament\Resources\Producers\Pages;

use App\Filament\Resources\Producers\ProducerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducers extends ListRecords
{
    protected static string $resource = ProducerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
