<?php

namespace App\Http\Controllers;

use App\Instruments;
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

    public function setInstrument()
    {
        return view('profile.set-instrument');
    }

    public function saveInstrument(Request $request)
    {
        $request->validate([
            'instrument' => ['required', Rule::in(array_keys(Instruments::INSTRUMENT_GROUPS))]
        ]);

        $request->user()->personalData->instrument = $request->input('instrument');
        $request->user()->personalData->save();

        /** @var User[] $admins */
        $admins = Role::findByName(Rights::R_ADMIN, 'web')->users()->get();
        foreach ($admins as $admin) {
            $admin->notify(new UserIsWaitingForActivation($request->user()));
        }

        return redirect(route('dashboard'));
    }
}
