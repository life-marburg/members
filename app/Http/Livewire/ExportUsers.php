<?php

namespace App\Http\Livewire;

use App\Exports\UsersExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ExportUsers extends Component
{
    public function render()
    {
        return view('livewire.export-users');
    }

    public function export()
    {
        return Excel::download(new UsersExport(), 'Mitglieder_' . now() . '.xlsx');
    }
}
