<?php

namespace App\Http\Controllers;

use App\Models\PersonalData;
use Illuminate\Http\Request;

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
}
