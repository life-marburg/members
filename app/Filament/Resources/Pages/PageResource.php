<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Rights;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string
    {
        return __('Pages');
    }

    public static function getModelLabel(): string
    {
        return __('Page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pages');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(Rights::R_ADMIN);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('path')
                    ->label(__('Path'))
                    ->content(fn ($record) => '/' . $record->path),
                RichEditor::make('content')
                    ->label(__('Content'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('path')
                    ->label(__('Path'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '/' . $state),
                TextColumn::make('updated_at')
                    ->label(__('Last Modified'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('path')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
