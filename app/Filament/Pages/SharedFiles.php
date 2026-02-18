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
                'group_name' => $sf->group?->name ?? __('Blocked'),
                'is_blocked' => $sf->group_id === null,
            ])
            ->toArray();
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
                    'groups' => $parentShares->map(fn ($sf) => $sf->group?->name ?? __('Blocked'))->toArray(),
                ];
            }
        }

        return null;
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

    public function addGroupShare(): void
    {
        if (! $this->selectedGroupId || ! $this->shareFolderPath) {
            return;
        }

        SharedFolder::create([
            'path' => $this->shareFolderPath,
            'group_id' => $this->selectedGroupId,
        ]);

        $this->selectedGroupId = null;
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
