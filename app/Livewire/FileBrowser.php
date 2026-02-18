<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\PathTraversalDetected;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use MWGuerra\FileManager\Adapters\AdapterFactory;
use MWGuerra\FileManager\Adapters\StorageAdapter;

#[Layout('layouts.app', ['pageTitle' => 'Files'])]
class FileBrowser extends Component
{
    #[Url(as: 'path')]
    public string $currentPath = '';

    protected function getAdapter(): StorageAdapter
    {
        return AdapterFactory::makeStorage();
    }

    #[Computed]
    public function items()
    {
        return $this->getAdapter()->getItems($this->currentPath ?: null);
    }

    #[Computed]
    public function breadcrumbs(): array
    {
        $breadcrumbs = $this->getAdapter()->getBreadcrumbs($this->currentPath ?: null);

        if (! empty($breadcrumbs) && ($breadcrumbs[0]['name'] ?? '') === 'Root') {
            $breadcrumbs[0]['name'] = __('Root');
        }

        return $breadcrumbs;
    }

    public function navigateTo(?string $path = null): void
    {
        $this->currentPath = $path ? ltrim($path, '/') : '';
        unset($this->items, $this->breadcrumbs);
    }

    public function download(string $path)
    {
        try {
            $adapter = $this->getAdapter();

            if (! $adapter->isPathSafe($path) || ! $adapter->exists($path)) {
                abort(404);
            }

            return Storage::disk('shared')->download($path);
        } catch (PathTraversalDetected) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.file-browser');
    }
}
