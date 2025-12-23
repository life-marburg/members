<?php

namespace App\Filament\Resources\ExternalLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExternalLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label(__('URL'))
                    ->limit(40)
                    ->searchable(),

                IconColumn::make('show_external_icon')
                    ->boolean()
                    ->label(__('Icon')),

                TextColumn::make('position')
                    ->label(__('Position'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('Active')),

                TextColumn::make('target')
                    ->label(__('Target'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '_blank' => 'info',
                        '_self' => 'gray',
                    }),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
            ])
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
}
