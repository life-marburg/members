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
    }

    protected function save()
    {
        $this->user->personalData->instrument = $this->state['instrument'];
        $this->user->personalData->save();

        $status = (int)$this->state['status'];
        if ($this->user->status != $status && $status === User::STATUS_UNLOCKED) {
            $this->user->notify(new UserStatusChanged());
        }

        if ($this->state['is_admin']) {
            $this->user->assignRole(Rights::R_ADMIN);
            $this->user->disable_after_days = null;
        } else {
            $this->user->removeRole(Rights::R_ADMIN);
            $this->user->disable_after_days = 90;
        }

        $this->user->status = $this->state['status'];
        $this->user->save();
    }
}
