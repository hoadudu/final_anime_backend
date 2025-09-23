<?php

namespace App\Filament\Resources\Billboards\Schemas;


use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Billboard;

class BillboardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('post_id')
                    ->relationship('post', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title ?? $record->getDisplayTitleAttribute() ?? 'Untitled'),
                TextInput::make('backdrop_image')
                    ->required()
                    ->url(),
                TextInput::make('position')                    
                    ->numeric()
                    ->default(Billboard::getNextPosition()),
            ]);
    }
}
