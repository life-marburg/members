<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Rights;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $page = Page::wherePath('/dashboard')->first();

        return view('dashboard', [
            'content' => $page->content,
            'canEdit' => Auth::user()->hasPermissionTo(Rights::P_EDIT_PAGES),
        ]);
    }
}
