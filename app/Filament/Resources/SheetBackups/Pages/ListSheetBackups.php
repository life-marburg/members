<?php

namespace App\Filament\Resources\SheetBackups\Pages;

use App\Filament\Resources\SheetBackups\SheetBackupResource;
use App\Jobs\CreateSheetBackupJob;
use App\Models\SheetBackup;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSheetBackups extends ListRecords
{
    protected static string $resource = SheetBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label(__('Create new backup'))
                ->icon('heroicon-o-plus')
                ->disabled(fn (): bool => $this->hasRunningBackup())
                ->tooltip(fn (): ?string => $this->hasRunningBackup() ? __('A backup is already running') : null)
                ->action(function (): void {
                    if ($this->hasRunningBackup()) {
                        return;
                    }

                    $backup = SheetBackup::create([
                        'status' => SheetBackup::STATUS_PENDING,
                        'created_by' => auth()->id(),
                    ]);

                    CreateSheetBackupJob::dispatch($backup->id);

                    Notification::make()
                        ->title(__('Backup queued'))
                        ->body(__('You will receive an email when it is ready.'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function hasRunningBackup(): bool
    {
        return SheetBackup::query()
            ->whereIn('status', [SheetBackup::STATUS_PENDING, SheetBackup::STATUS_IN_PROGRESS])
            ->exists();
    }
}
