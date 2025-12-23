<?php

namespace App\Filament\Resources\SongSets\Pages;

use App\Filament\Resources\SongSets\SongSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSongSets extends ListRecords
{
    protected static string $resource = SongSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
