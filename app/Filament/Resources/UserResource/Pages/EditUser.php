<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Rights;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Contracts\DeletesUsers;

/**
 * @property User $record
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading(__('Are you sure you want to delete the account of :user?', ['user' => $this->record->name]))
                ->modalSubheading(__('Once this account is deleted, all of its resources and data will be permanently deleted. The user will not be able to log in or use it.'))
                ->using(function () {
                    $deleter = resolve(DeletesUsers::class);
                    $deleter->delete($this->record->fresh());
                    return true;
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['is_admin'] = $this->record->hasRole(Rights::R_ADMIN);
        $data['can_view_all_instruments'] = $data['is_admin'] || $this->record->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);

        $data['street'] = $this->record->personalData->street;
        $data['zip'] = $this->record->personalData->zip;
        $data['city'] = $this->record->personalData->city;
        $data['phone'] = $this->record->personalData->phone;
        $data['mobile_phone'] = $this->record->personalData->mobile_phone;

        return $data;
    }

    /**
     * @param User $record
     * @param array $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['disable_after_days'] = $data['disable_after_days'] === 'null' ? null : $data['disable_after_days'];
        $record->update($data);

        $record
            ->personalData
            ->fill([
                'street' => $data['street'],
                'city' => $data['city'],
                'zip' => $data['zip'],
                'phone' => $data['phone'] ?? null,
                'mobile_phone' => $data['mobile_phone'],
            ])
            ->save();

        $status = (int)$data['status'];
        if ($record->status != $status && $status === User::STATUS_UNLOCKED) {
            $record->notify(new UserStatusChanged());
        }

        if ($record->hasRole(Rights::R_ADMIN) !== boolval($data['is_admin'])) {
            if ($data['is_admin']) {
                $record->assignRole(Rights::R_ADMIN);
                $record->disable_after_days = null;
            } else {
                $record->removeRole(Rights::R_ADMIN);
                $record->disable_after_days = 90;
            }
        }

        if (!$record->hasRole(Rights::R_ADMIN) &&
            $record->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS) !== boolval($data['can_view_all_instruments'])) {
            if ($data['can_view_all_instruments']) {
                $record->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
            } else {
                $record->revokePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
            }
        }

        $record->status = $data['status'];
        $record->save();

        return $record;
    }
}
