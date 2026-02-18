<?php

namespace App\Livewire;

use App\Services\SharedFolderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use League\Flysystem\PathTraversalDetected;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url as LivewireUrl;
use Livewire\Component;
use MWGuerra\FileManager\Adapters\AdapterFactory;
use MWGuerra\FileManager\Adapters\StorageAdapter;

#[Layout('layouts.app', ['pageTitle' => 'Files'])]
class FileBrowser extends Component
{
    #[LivewireUrl(as: 'path')]
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

        // Inside a folder: show items based on access level
        $allItems = $this->getAdapter()->getItems($this->currentPath);
        $hasDirectAccess = $service->canAccess($user, $this->currentPath);

        return $allItems->filter(function ($item) use ($user, $service, $hasDirectAccess) {
            if ($item->isFolder()) {
                return $service->canNavigate($user, $item->getIdentifier());
            }

            // Only show files if user has direct access to this folder
            return $hasDirectAccess;
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
            if (! $this->sharingService()->canNavigate($user, $path)) {
                abort(403);
            }
        }

        $this->currentPath = $path;
        unset($this->items, $this->breadcrumbs);
    }

    public function download(string $path)
    {
        if (str_contains($path, '..')) {
            abort(404);
        }

        try {
            $adapter = $this->getAdapter();

            if (! $adapter->isPathSafe($path) || ! $adapter->exists($path)) {
                abort(404);
            }

            $user = Auth::user();
            if (! $this->sharingService()->canAccess($user, $path)) {
                abort(403);
            }

            return $this->redirect(URL::signedRoute('files.download', ['path' => $path]));
        } catch (PathTraversalDetected) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.file-browser');
    }
}
