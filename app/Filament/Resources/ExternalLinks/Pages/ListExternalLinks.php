<?php

namespace App\Filament\Resources\ExternalLinks\Pages;

use App\Filament\Resources\ExternalLinks\ExternalLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalLinks extends ListRecords
{
    protected static string $resource = ExternalLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
