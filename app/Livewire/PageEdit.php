<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class PageEdit extends Component
{
    public Page $page;
    public string $content = '';

    public function mount()
    {
        $this->content = $this->page->content;
    }

    public function update()
    {
        $this->page->content = $this->content;
        $this->page->save();
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.page-edit');
    }
}
