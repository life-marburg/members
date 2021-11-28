<?php

namespace App\Http\Controllers;

use App\Models\Page;

class DashboardController extends Controller
{
    public function index()
    {
        $page = Page::wherePath('/dashboard')->first();

        return view('dashboard', ['content' => $page->content]);
    }
}
