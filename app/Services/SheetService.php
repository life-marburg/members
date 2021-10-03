<?php

namespace App\Services;

use App\Instruments;
use Illuminate\Support\Collection;

class SheetService
{
    protected function getSongFileStructure(): array
    {
        return [];
    }

    public function getSheetsForInstrument(string $instrumentGroup): ?Collection
    {
        if (!isset(Instruments::INSTRUMENT_GROUPS[$instrumentGroup])) {
            return null;
        }

        $instruments = Instruments::INSTRUMENT_GROUPS[$instrumentGroup]['instruments'];
        $structure = collect($this->getSongFileStructure());

        return $structure->map(function ($item, $key) use ($instruments) {
            $all = [];
            foreach ($item as $it) {
                foreach ($instruments as $int) {
                    if (str_contains($it, $int)) {
                        $all[] = $it;
                    }
                }
            }

            return $all;
        });
    }
}
