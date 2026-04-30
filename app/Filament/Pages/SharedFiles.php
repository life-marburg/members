<?php

namespace App\Filament\Pages;

use App\Models\Group;
use App\Models\SharedFolder;
use Illuminate\Contracts\Support\Htmlable;
use MWGuerra\FileManager\Filament\Pages\FileSystem;

class SharedFiles extends FileSystem
{
    public ?string $shareFolderPath = null;

    public ?string $shareFolderName = null;

    public ?string $selectedGroupId = null;

    public bool $isPublic = false;

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationLabel(): string
    {
        return __('Files');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Files');
    }

    public function getSidebarRootLabel(): string
    {
        return __('Root');
    }

    public function getSidebarHeading(): string
    {
        return __('Folders');
    }

    public function getBreadcrumbsProperty(): array
    {
        $breadcrumbs = parent::getBreadcrumbsProperty();

        if (! empty($breadcrumbs) && ($breadcrumbs[0]['name'] ?? '') === 'Root') {
            $breadcrumbs[0]['name'] = __('Root');
        }

        return $breadcrumbs;
    }

    public function openShareDialog(string $folderId): void
    {
        $this->shareFolderPath = $folderId;
        $this->shareFolderName = basename($folderId);
        $this->selectedGroupId = null;
        $this->isPublic = false;
    }

    public function getSharesProperty(): array
    {
        if (! $this->shareFolderPath) {
            return [];
        }

        return SharedFolder::where('path', $this->shareFolderPath)
            ->with('group')
            ->get()
            ->map(fn ($sf) => [
                'id' => $sf->id,
                'group_name' => $this->shareLabel($sf),
                'is_blocked' => $sf->group_id === null && ! $sf->is_public,
                'is_public' => $sf->is_public,
            ])
            ->toArray();
    }

    private function shareLabel(SharedFolder $share): string
    {
        if ($share->is_public) {
            return __('Everyone');
        }

        return $share->group?->name ?? __('Blocked');
    }

    public function getInheritedShareInfoProperty(): ?array
    {
        if (! $this->shareFolderPath) {
            return null;
        }

        // Check if there are direct shares
        if (SharedFolder::where('path', $this->shareFolderPath)->exists()) {
            return null;
        }

        // Walk up to find inherited shares
        $segments = explode('/', $this->shareFolderPath);

        for ($i = count($segments) - 1; $i >= 1; $i--) {
            $parentPath = implode('/', array_slice($segments, 0, $i));
            $parentShares = SharedFolder::where('path', $parentPath)->with('group')->get();

            if ($parentShares->isNotEmpty()) {
                return [
                    'path' => $parentPath,
                    'groups' => $parentShares->map(fn ($sf) => $this->shareLabel($sf))->toArray(),
                ];
            }
        }

        return null;
    }

    public function getIsRootFolderProperty(): bool
    {
        if (! $this->shareFolderPath) {
            return false;
        }

        return ! str_contains($this->shareFolderPath, '/');
    }

    public function getAvailableGroupsProperty(): array
    {
        $existingGroupIds = SharedFolder::where('path', $this->shareFolderPath)
            ->whereNotNull('group_id')
            ->pluck('group_id');

        return Group::whereNotIn('id', $existingGroupIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getHasPublicShareProperty(): bool
    {
        if (! $this->shareFolderPath) {
            return false;
        }

        return SharedFolder::where('path', $this->shareFolderPath)
            ->where('is_public', true)
            ->exists();
    }

    public function addShare(): void
    {
        if (! $this->shareFolderPath) {
            return;
        }

        if ($this->isPublic) {
            SharedFolder::create([
                'path' => $this->shareFolderPath,
                'group_id' => null,
                'is_public' => true,
            ]);
        } elseif ($this->selectedGroupId) {
            SharedFolder::create([
                'path' => $this->shareFolderPath,
                'group_id' => $this->selectedGroupId,
            ]);
        } else {
            return;
        }

        $this->selectedGroupId = null;
        $this->isPublic = false;
    }

    public function blockFolder(): void
    {
        if (! $this->shareFolderPath) {
            return;
        }

        // Remove existing shares and add a block record
        SharedFolder::where('path', $this->shareFolderPath)->delete();
        SharedFolder::create([
            'path' => $this->shareFolderPath,
            'group_id' => null,
        ]);
    }

    public function removeShare(int $shareId): void
    {
        SharedFolder::where('id', $shareId)->delete();
    }

    public function removeAllShares(): void
    {
        if (! $this->shareFolderPath) {
            return;
        }

        SharedFolder::where('path', $this->shareFolderPath)->delete();
    }
}
