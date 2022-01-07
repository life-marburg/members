<?php

namespace App\Http\Controllers;

class PersonalDataController extends Controller
{
    public function edit()
    {
        return view('profile.manage-personal-data');
    }

    public function set()
    {
        return view('pages.personal-data.form');
    }

    public function save()
    {

    }
}
