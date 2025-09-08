<?php

namespace App\Filament\Resources\UserAnimeListItems\Pages;

use App\Filament\Resources\UserAnimeListItems\UserAnimeListItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserAnimeListItem extends CreateRecord
{
    protected static string $resource = UserAnimeListItemResource::class;
}
