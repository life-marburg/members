<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdatePersonalDataForm extends Component
{
    public $state = [];

    public function mount()
    {
        $this->state = Auth::user()->personalData->toArray();
        $this->state['name'] = Auth::user()->name;
    }

    public function update()
    {
        $this->resetErrorBag();

        Auth::user()
            ->personalData
            ->fill([
                'street' => $this->state['street'],
                'city' => $this->state['city'],
                'zip' => $this->state['zip'],
            ])
            ->save();

        Auth::user()->name = $this->state['name'];
        Auth::user()->save();

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.update-personal-data-form');
    }
}
