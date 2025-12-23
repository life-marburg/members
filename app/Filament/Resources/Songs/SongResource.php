<?php

namespace App\Filament\Resources\Songs;

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Filament\Resources\Songs\Pages\ListSongs;
use App\Filament\Resources\Songs\RelationManagers\SheetsRelationManager;
use App\Models\Song;
use App\Rights;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SongResource extends Resource
{
    protected static ?string $model = Song::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-musical-note';

    public static function getNavigationLabel(): string
    {
        return __('Sheets');
    }

    public static function getModelLabel(): string
    {
        return __('Song');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Songs');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Rights::P_MANAGE_SHEETS);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sheets_count')
                    ->counts('sheets')
                    ->label(__('Sheets')),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('title')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SheetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSongs::route('/'),
            'create' => CreateSong::route('/create'),
            'edit' => EditSong::route('/{record}/edit'),
        ];
    }
}
