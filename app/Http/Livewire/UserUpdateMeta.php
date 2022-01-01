<?php

namespace App\Http\Livewire;

use App\Rights;
use Livewire\Component;

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

        $this->user->status = $this->state['status'];
        $this->user->save();

        if ($this->state['is_admin']) {
            $this->user->assignRole(Rights::R_ADMIN);
        } else {
            $this->user->removeRole(Rights::R_ADMIN);
        }
    }
}
