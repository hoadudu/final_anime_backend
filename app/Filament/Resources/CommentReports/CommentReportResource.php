<?php

namespace App\Filament\Resources\CommentReports;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\CommentReport;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NavigationSort;
use App\Filament\Resources\CommentReports\Pages\EditCommentReport;
use App\Filament\Resources\CommentReports\Pages\ListCommentReports;
use App\Filament\Resources\CommentReports\Pages\CreateCommentReport;
use App\Filament\Resources\CommentReports\Schemas\CommentReportForm;
use App\Filament\Resources\CommentReports\Tables\CommentReportsTable;

class CommentReportResource extends Resource
{
    protected static ?string $model = CommentReport::class;

    protected static ?string $navigationLabel = 'Comment Reports';

    protected static ?int $navigationSort = NavigationSort::COMMENT_REPORTS;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CommentReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommentReports::route('/'),
            'create' => CreateCommentReport::route('/create'),
            'edit' => EditCommentReport::route('/{record}/edit'),
        ];
    }
}
