<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Rights;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['is_admin'] = $this->record->hasRole(Rights::R_ADMIN);
        $data['can_view_all_instruments'] = $this->record->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        // Handle admin role
        if ($data['is_admin'] ?? false) {
            $this->record->assignRole(Rights::R_ADMIN);
            $this->record->update(['disable_after_days' => null]);
        } else {
            $this->record->removeRole(Rights::R_ADMIN);
            if ($this->record->disable_after_days === null) {
                $this->record->update(['disable_after_days' => 90]);
            }
        }

        // Handle view all instruments permission
        if ($data['can_view_all_instruments'] ?? false) {
            $this->record->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        } else {
            $this->record->revokePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        }
    }
}
