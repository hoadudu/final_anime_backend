<?php

namespace App\Filament\Resources\Billboards\Pages;

use App\Filament\Resources\Billboards\BillboardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBillboard extends EditRecord
{
    protected static string $resource = BillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
