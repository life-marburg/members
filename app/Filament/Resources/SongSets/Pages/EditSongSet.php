<?php

namespace App\Filament\Resources\SongSets\Pages;

use App\Filament\Resources\SongSets\SongSetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSongSet extends EditRecord
{
    protected static string $resource = SongSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
