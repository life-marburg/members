<?php

namespace App\Filament\Pages;

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
}
