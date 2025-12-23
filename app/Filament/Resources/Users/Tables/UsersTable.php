<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Exports\UserExporter;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Rights;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('instrumentGroups.title')
                    ->badge()
                    ->separator(', ')
                    ->label(__('Instruments')),
                TextColumn::make('personalData.city')
                    ->label(__('City'))
                    ->sortable(),
                TextColumn::make('personalData.mobile_phone')
                    ->label(__('Mobile')),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        User::STATUS_NEW => __('New'),
                        User::STATUS_UNLOCKED => __('Active'),
                        User::STATUS_LOCKED => __('Locked'),
                        default => __('Unknown'),
                    })
                    ->color(fn (int $state): string => match ($state) {
                        User::STATUS_NEW => 'warning',
                        User::STATUS_UNLOCKED => 'success',
                        User::STATUS_LOCKED => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_admin')
                    ->label(__('Admin'))
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->hasRole(Rights::R_ADMIN)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        User::STATUS_NEW => __('New'),
                        User::STATUS_UNLOCKED => __('Active'),
                        User::STATUS_LOCKED => __('Locked'),
                    ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('activate')
                    ->label(__('Activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === User::STATUS_NEW)
                    ->action(function (User $record): void {
                        $record->update(['status' => User::STATUS_UNLOCKED]);
                        $record->notify(new UserStatusChanged());
                    }),
                Action::make('lock')
                    ->label(__('Lock'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === User::STATUS_UNLOCKED)
                    ->action(fn (User $record) => $record->update(['status' => User::STATUS_LOCKED])),
                Action::make('unlock')
                    ->label(__('Unlock'))
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === User::STATUS_LOCKED)
                    ->action(fn (User $record) => $record->update(['status' => User::STATUS_UNLOCKED])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('Activate Selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(function (User $user) {
                            $user->update(['status' => User::STATUS_UNLOCKED]);
                            $user->notify(new UserStatusChanged());
                        })),
                    BulkAction::make('lock')
                        ->label(__('Lock Selected'))
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => User::STATUS_LOCKED])),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->can(Rights::P_DELETE_ACCOUNTS)),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
