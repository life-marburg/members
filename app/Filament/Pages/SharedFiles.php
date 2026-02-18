<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use MWGuerra\FileManager\Filament\Pages\FileSystem;

class SharedFiles extends FileSystem
{
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
}
