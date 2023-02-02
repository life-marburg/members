<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MembersList extends Component
{
    use WithPagination;

    public string $sortBy = 'id';

    protected array $rules = [
        'sortBy' => 'in_array:id,name,instrument,street,zip,city',
    ];

    protected $queryString = ['sortBy'];

    public function sort()
    {
        // Actual sorting happens in render
        $this->validate();
        $this->resetPage();
    }

    public function render()
    {
        $members = User::with('personalData')
            ->orderBy($this->sortBy);

        if ($this->sortBy === 'instrument') {
            $members = User::select('users.*')
                ->distinct()
                ->join('personal_data', 'users.id', '=', 'personal_data.user_id')
                ->leftJoin('user_instrument_group', 'users.id', '=', 'user_instrument_group.user_id')
                ->leftJoin('instrument_groups', 'user_instrument_group.instrument_group_id', '=', 'instrument_groups.id')
                ->orderBy('instrument_groups.title');
        }

        if (in_array($this->sortBy, ['street', 'city', 'zip'])) {
            $members = User::select('users.*')
                ->join('personal_data', 'users.id', '=', 'personal_data.user_id')
                ->orderBy('personal_data.'.$this->sortBy);
        }

        return view('livewire.members-list', [
            'members' => $members->paginate(50),
        ]);
    }
}
