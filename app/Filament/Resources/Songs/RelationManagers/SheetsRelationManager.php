<?php

namespace App\Filament\Resources\Songs\RelationManagers;

use App\Models\Instrument;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class SheetsRelationManager extends RelationManager
{
    protected static string $relationship = 'sheets';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Sheets');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_title')
            ->columns([
                TextColumn::make('instrument.title')
                    ->label(__('Instrument'))
                    ->sortable(),
                TextColumn::make('part_number')
                    ->label(__('Part'))
                    ->sortable(),
                TextColumn::make('variant')
                    ->label(__('Variant')),
                TextColumn::make('created_at')
                    ->label(__('Uploaded'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('instrument.title')
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Select::make('instrument_id')
                            ->label(__('Instrument'))
                            ->options(Instrument::all()->pluck('title', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('part_number')
                            ->label(__('Part Number'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99),
                        TextInput::make('variant')
                            ->label(__('Variant'))
                            ->maxLength(100),
                        FileUpload::make('file_path')
                            ->label(__('PDF File'))
                            ->disk('sheets')
                            ->directory(fn ($livewire) => 'song-'.$livewire->ownerRecord->id)
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        Select::make('instrument_id')
                            ->label(__('Instrument'))
                            ->options(Instrument::all()->pluck('title', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('part_number')
                            ->label(__('Part Number'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99),
                        TextInput::make('variant')
                            ->label(__('Variant'))
                            ->maxLength(100),
                    ]),
                DeleteAction::make()
                    ->after(function ($record) {
                        Storage::disk('sheets')->delete($record->file_path);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function ($records) {
                            foreach ($records as $record) {
                                Storage::disk('sheets')->delete($record->file_path);
                            }
                        }),
                ]),
            ]);
    }
}
