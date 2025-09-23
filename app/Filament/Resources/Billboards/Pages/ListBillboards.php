<?php

namespace App\Filament\Resources\Billboards\Pages;

use App\Filament\Resources\Billboards\BillboardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBillboards extends ListRecords
{
    protected static string $resource = BillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
