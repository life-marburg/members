<?php

namespace App\Filament\Resources\ExternalLinks;

use App\Filament\Resources\ExternalLinks\Pages\CreateExternalLink;
use App\Filament\Resources\ExternalLinks\Pages\EditExternalLink;
use App\Filament\Resources\ExternalLinks\Pages\ListExternalLinks;
use App\Filament\Resources\ExternalLinks\Schemas\ExternalLinkForm;
use App\Filament\Resources\ExternalLinks\Tables\ExternalLinksTable;
use App\Models\ExternalLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExternalLinkResource extends Resource
{
    protected static ?string $model = ExternalLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    public static function getNavigationLabel(): string
    {
        return __('External Links');
    }

    public static function getModelLabel(): string
    {
        return __('External Link');
    }

    public static function getPluralModelLabel(): string
    {
        return __('External Links');
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalLinksTable::configure($table);
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
            'index' => ListExternalLinks::route('/'),
            'create' => CreateExternalLink::route('/create'),
            'edit' => EditExternalLink::route('/{record}/edit'),
        ];
    }
}
