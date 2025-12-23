<?php

namespace App\Filament\Resources\SongSets;

use App\Filament\Resources\SongSets\Pages\CreateSongSet;
use App\Filament\Resources\SongSets\Pages\EditSongSet;
use App\Filament\Resources\SongSets\Pages\ListSongSets;
use App\Filament\Resources\SongSets\RelationManagers\SongsRelationManager;
use App\Models\SongSet;
use App\Rights;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SongSetResource extends Resource
{
    protected static ?string $model = SongSet::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    public static function getNavigationLabel(): string
    {
        return __('Song Sets');
    }

    public static function getModelLabel(): string
    {
        return __('Song Set');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Song Sets');
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
                Toggle::make('is_new')
                    ->label(__('New')),
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
                IconColumn::make('is_new')
                    ->label(__('New'))
                    ->boolean(),
                TextColumn::make('songs_count')
                    ->counts('songs')
                    ->label(__('Songs')),
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
            SongsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSongSets::route('/'),
            'create' => CreateSongSet::route('/create'),
            'edit' => EditSongSet::route('/{record}/edit'),
        ];
    }
}
