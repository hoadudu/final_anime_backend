<?php

namespace App\Filament\Resources\CommentReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CommentReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('comment_id')
                    ->relationship('comment', 'id')
                    ->required(),
                Select::make('reason')
                    ->options([
            'spam' => 'Spam',
            'inappropriate' => 'Inappropriate',
            'harassment' => 'Harassment',
            'other' => 'Other',
        ])
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'])
                    ->default('pending')
                    ->required(),
                TextInput::make('resolved_by')
                    ->numeric(),
                DateTimePicker::make('resolved_at'),
            ]);
    }
}
