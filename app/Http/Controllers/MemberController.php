<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        return view('pages.members.index');
    }

    public function edit(User $member)
    {
        return view('pages.members.edit', [
            'member' => $member,
        ]);
    }
}
