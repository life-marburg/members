<?php

namespace App\Filament\Resources\SheetBackups;

use App\Filament\Resources\SheetBackups\Pages\ListSheetBackups;
use App\Models\SheetBackup;
use App\Rights;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;

class SheetBackupResource extends Resource
{
    protected static ?string $model = SheetBackup::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    public static function getNavigationLabel(): string
    {
        return __('Sheet backups');
    }

    public static function getModelLabel(): string
    {
        return __('Sheet backup');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sheet backups');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(Rights::R_ADMIN) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label(__('Created by')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __($state))
                    ->color(fn (string $state): string => match ($state) {
                        SheetBackup::STATUS_PENDING => 'gray',
                        SheetBackup::STATUS_IN_PROGRESS => 'info',
                        SheetBackup::STATUS_READY => 'success',
                        SheetBackup::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('file_size')
                    ->label(__('Size'))
                    ->formatStateUsing(fn ($state): string => $state ? Number::fileSize((int) $state, 1) : '—'),
                TextColumn::make('sheet_count')
                    ->label(__('Sheets'))
                    ->formatStateUsing(fn ($state): string => $state !== null ? (string) $state : '—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('download')
                    ->label(__('Download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (SheetBackup $record): bool => $record->status === SheetBackup::STATUS_READY)
                    ->url(fn (SheetBackup $record): string => URL::signedRoute(
                        'sheet-backups.download',
                        ['backup' => $record->id],
                        now()->addDays(7),
                    ))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->after(function (SheetBackup $record): void {
                        if ($record->file_path) {
                            Storage::disk('sheet-backups')->delete($record->file_path);
                        }
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSheetBackups::route('/'),
        ];
    }
}
