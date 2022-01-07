<?php

namespace App\Http\Controllers;

class PersonalDataController extends Controller
{
    public function set()
    {
        return view('pages.personal-data.form');
    }
}
