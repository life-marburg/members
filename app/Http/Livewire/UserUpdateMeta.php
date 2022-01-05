<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Rights;

class UserUpdateMeta extends UserEditComponent
{
    public function render()
    {
        return view('livewire.user-update-meta');
    }

    public function mount()
    {
        parent::mount();

        $this->state['status'] = $this->user->status;
        $this->state['instrument'] = $this->user->personalData->instrument;
        $this->state['is_admin'] = $this->user->hasRole(Rights::R_ADMIN);
        $this->state['disable_after'] = $this->user->disable_after_days;
        $this->state['can_view_all_instruments'] = $this->user->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
    }

    protected function save()
    {
        $this->user->personalData->instrument = $this->state['instrument'];
        $this->user->personalData->save();
        $this->user->disable_after_days = $this->state['disable_after'] === 'null' ? null : $this->state['disable_after'];

        $status = (int)$this->state['status'];
        if ($this->user->status != $status && $status === User::STATUS_UNLOCKED) {
            $this->user->notify(new UserStatusChanged());
        }

        if ($this->user->hasRole(Rights::R_ADMIN) !== boolval($this->state['is_admin'])) {
            if ($this->state['is_admin']) {
                $this->user->assignRole(Rights::R_ADMIN);
                $this->user->disable_after_days = null;
            } else {
                $this->user->removeRole(Rights::R_ADMIN);
                $this->user->disable_after_days = 90;
            }
        }

        if (!$this->user->hasRole(Rights::R_ADMIN) &&
            $this->user->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS) !== boolval($this->state['can_view_all_instruments'])) {
            if ($this->state['can_view_all_instruments']) {
                $this->user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
            } else {
                $this->user->revokePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
            }
        }

        $this->user->status = $this->state['status'];
        $this->user->save();
    }
}
