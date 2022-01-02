<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function edit(Page $page)
    {
        return view('pages.page-edit', [
            'page' => $page,
        ]);
    }
}
