<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Rights;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Groups');
    }

    public function table(Table $table): Table
    {
        // Groups grant shared-folder access, so membership changes need P_MANAGE_GROUPS, not just P_MANAGE_MEMBERS.
        $canManageGroups = fn (): bool => auth()->user()->hasPermissionTo(Rights::P_MANAGE_GROUPS);

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name')),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->visible($canManageGroups),
            ])
            ->recordActions([
                DetachAction::make()
                    ->visible($canManageGroups),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->visible($canManageGroups),
                ]),
            ]);
    }
}
