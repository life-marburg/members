<?php

namespace App\Http\Controllers;

use App\Instruments;
use App\Models\InstrumentGroup;
use App\Models\PersonalData;
use App\Models\User;
use App\Notifications\UserIsWaitingForActivation;
use App\Rights;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class PersonalDataController extends Controller
{
    public function edit(PersonalData $personalData)
    {
        return view('profile.manage-personal-data');
    }
}
