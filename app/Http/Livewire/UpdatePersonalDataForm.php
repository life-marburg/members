<?php

namespace App\Http\Livewire;

class UpdatePersonalDataForm extends UserEditComponent
{
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

    public function __construct($id = null)
    {
        parent::__construct($id);

        foreach ($this->validationAttributes as $key => $value) {
            $this->validationAttributes[$key] = __($value);
        }
    }

    public function mount()
    {
        parent::mount();

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
