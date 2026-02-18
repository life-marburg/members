<?php

namespace App\Livewire;

use App\Services\SharedFolderService;
use Illuminate\Support\Facades\Auth;
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

    protected function sharingService(): SharedFolderService
    {
        return app(SharedFolderService::class);
    }

    #[Computed]
    public function items()
    {
        $user = Auth::user();
        $service = $this->sharingService();

        // Root level: show only shared root folders
        if ($this->currentPath === '') {
            $rootPaths = $service->getAccessibleRootPaths($user);
            $allItems = $this->getAdapter()->getItems(null);

            return $allItems->filter(
                fn ($item) => $item->isFolder() && $rootPaths->contains($item->getIdentifier())
            )->values();
        }

        // Inside a folder: show all items, filtering out blocked subfolders
        $allItems = $this->getAdapter()->getItems($this->currentPath);

        return $allItems->filter(function ($item) use ($user, $service) {
            if (! $item->isFolder()) {
                return true;
            }

            return $service->canAccess($user, $item->getIdentifier());
        })->values();
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
        $path = $path ? ltrim($path, '/') : '';

        // Check access for non-root navigation
        if ($path !== '') {
            $user = Auth::user();
            if (! $this->sharingService()->canAccess($user, $path)) {
                abort(403);
            }
        }

        $this->currentPath = $path;
        unset($this->items, $this->breadcrumbs);
    }

    public function render()
    {
        return view('livewire.file-browser');
    }
}
