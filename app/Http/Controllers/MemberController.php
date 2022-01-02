<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $members = User::with('personalData')->paginate(25);

        return view('pages.members.index', [
            'members' => $members,
        ]);
    }

    public function edit(User $member)
    {
        return view('pages.members.edit', [
            'member' => $member,
        ]);
    }
}
