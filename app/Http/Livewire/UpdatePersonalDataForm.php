<?php

namespace App\Http\Livewire;

class UpdatePersonalDataForm extends UserEditComponent
{
    public function mount()
    {
        parent::mount();

        $this->state = $this->user->personalData->toArray();
        $this->state['name'] = $this->user->name;
    }

    protected function save()
    {
        $this->user
            ->personalData
            ->fill([
                'street' => $this->state['street'],
                'city' => $this->state['city'],
                'zip' => $this->state['zip'],
                'phone' => $this->state['phone'],
                'mobile_phone' => $this->state['mobile_phone'],
            ])
            ->save();

        $this->user->name = $this->state['name'];
        $this->user->save();
    }

    public function render()
    {
        return view('livewire.update-personal-data-form');
    }
}
