<?php

namespace App\Http\Controllers;

use Auth;

class PersonalDataController extends Controller
{
    public function set()
    {
        if (Auth::user()->hasPersonalData()) {
            return redirect(route('dashboard'));
        }

        return view('pages.personal-data.form');
    }
}
