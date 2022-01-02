<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public ?string $pageTitle = null;

    public function __construct(?string $pageTitle = null)
    {
        $this->pageTitle = $pageTitle;
    }

    public function render()
    {
        return view('layouts.app');
    }
}
