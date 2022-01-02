<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GuestLayout extends Component
{
    public ?string $pageTitle = null;

    public function __construct(string $pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    public function render()
    {
        return view('layouts.guest');
    }
}
