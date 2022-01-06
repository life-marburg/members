<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

abstract class UserEditComponent extends Component
{
    public array $state = [];
    public ?User $user = null;

    public function mount()
    {
        $this->user = $this->user ?? Auth::user();
    }

    public function update()
    {
        $this->resetErrorBag();

        DB::transaction(function () {
            $this->save();
        });

        $this->emit('saved');
    }

    abstract protected function save();
}
