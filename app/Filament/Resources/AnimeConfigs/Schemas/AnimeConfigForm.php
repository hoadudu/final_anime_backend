<?php

namespace App\Filament\Resources\AnimeConfigs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\KeyValue;

class AnimeConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('config_key')
                    ->required(),
                KeyValue::make('config_value')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columns(2)
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'font-family: monospace;']),
                // Textarea::make('config_value')
                //     ->rows(10)
                //     ->columnSpanFull()
                //     ->extraAttributes(['style' => 'font-family: monospace;']),

                TextInput::make('description'),
            ]);
    }
}
