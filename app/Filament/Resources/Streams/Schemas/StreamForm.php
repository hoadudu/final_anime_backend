<?php

namespace App\Filament\Resources\Streams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StreamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('episode_id')
                    ->relationship('episode', 'id')
                    ->required(),
                TextInput::make('server_name'),
                TextInput::make('url'),
                TextInput::make('meta'),
                Select::make('stream_type')
                    ->options([
            'direct' => 'Direct',
            'embed' => 'Embed',
            'hls' => 'Hls',
            'm3u8' => 'M3u8',
            'dash' => 'Dash',
            'other' => 'Other',
        ])
                    ->default('direct')
                    ->required(),
                Select::make('quality')
                    ->options([
            '360p' => '360p',
            '480p' => '480p',
            '720p' => '720p',
            '1080p' => '1080p',
            '4k' => '4k',
            'auto' => 'Auto',
        ])
                    ->default('auto')
                    ->required(),
                Select::make('language')
                    ->options(['sub' => 'Sub', 'dub' => 'Dub', 'raw' => 'Raw'])
                    ->default('sub')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
