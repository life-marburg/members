<?php

namespace App\Livewire;

use App\Models\User;
use App\Notifications\UserIsWaitingForActivation;
use App\Rights;
use Spatie\Permission\Models\Role;

class UpdatePersonalDataForm extends UserEditComponent
{
    public bool $hasFormShell = true;

    public bool $notifyAdmin = false;

    protected array $rules = [
        'state.name' => 'required',
        'state.street' => 'required',
        'state.city' => 'required',
        'state.zip' => 'required',
        'state.mobile_phone' => 'required',
    ];

    protected array $validationAttributes = [
        'state.name' => 'First and lastname',
        'state.street' => 'Street and Housenumber',
        'state.city' => 'City',
        'state.zip' => 'Zip Code',
        'state.phone' => 'Phone',
        'state.mobile_phone' => 'Mobile Phone',
    ];

    public function mount()
    {
        parent::mount();

        // Translate validation attributes (moved from constructor - Livewire 3 doesn't allow custom constructors)
        foreach ($this->validationAttributes as $key => $value) {
            $this->validationAttributes[$key] = __($value);
        }

        $this->state = $this->user->personalData->toArray();
        $this->state['name'] = $this->user->name;
    }

    protected function save()
    {
        $this->validate();

        $this->user
            ->personalData
            ->fill([
                'street' => $this->state['street'],
                'city' => $this->state['city'],
                'zip' => $this->state['zip'],
                'phone' => $this->state['phone'] ?? null,
                'mobile_phone' => $this->state['mobile_phone'],
            ])
            ->save();

        $this->user->name = $this->state['name'];
        $this->user->save();

        if ($this->notifyAdmin) {
            /** @var User[] $admins */
            $admins = Role::findByName(Rights::R_ADMIN, 'web')->users()->get();
            foreach ($admins as $admin) {
                $admin->notify(new UserIsWaitingForActivation($this->user));
            }
        }
    }

    public function render()
    {
        return view('livewire.update-personal-data-form');
    }
}
