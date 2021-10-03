<?php

namespace App\Http\Controllers;

use App\Models\PersonalData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalDataController extends Controller
{
    public function index()
    {
        //
    }

    public function show(PersonalData $personalData)
    {
        //
    }

    public function edit(PersonalData $personalData)
    {
        return view('profile.manage-personal-data');
    }

    public function update(Request $request, PersonalData $personalData)
    {
        //
    }

    public function destroy(PersonalData $personalData)
    {
        //
    }

    public function setInstrument()
    {
        return view('profile.set-instrument');
    }

    public function saveInstrument(Request $request)
    {
        $request->user()->personalData->instrument = $request->input('instrument');
        $request->user()->personalData->save();

        return redirect(route('dashboard'));
    }
}
