<?php

namespace App\Services;

use App\Instruments;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SheetService
{
    private const SHEET_FOLDER = 'Life/Noten';
    private const CACHE_KEY = 'sheet_files';

    protected function getSongFileStructure(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $all = [];
            $files = Storage::disk('cloud')->allFiles(self::SHEET_FOLDER);

            foreach ($files as $file) {
                $file = str_replace(self::SHEET_FOLDER . '/', '', $file);
                $parts = explode('/', $file);
                if (count($parts) === 2) {
                    $all[$parts[0]][] = $parts[1];
                }
            }

            return $all;
        });
    }

    public function getSheetsForInstrument(string $instrumentGroup, string $instrument = null): ?Collection
    {
        if (!isset(Instruments::INSTRUMENT_GROUPS[$instrumentGroup])) {
            return null;
        }

        $instruments = Instruments::INSTRUMENT_GROUPS[$instrumentGroup]['instruments'];
        $structure = collect($this->getSongFileStructure());

        return $structure
            ->map(function ($item) use ($instruments, $instrument) {
                $all = [];
                foreach ($item as $it) {
                    if ($instrument === null) {
                        foreach ($instruments as $int) {
                            if (str_contains($it, $int)) {
                                $all[] = $it;
                            }
                        }
                    } else {
                        if (str_contains($it, $instrument)) {
                            $all[] = $it;
                        }
                    }
                }

                return $all;
            })
            ->filter(function ($item) {
                return count($item) > 0;
            })
            ->map(function ($item) {
                $all = [];
                foreach ($item as $i) {
                    // Stück.Instrument.Stimme.Variant
                    $parts = explode('.', $i);

                    if (count($parts) < 4) {
                        continue;
                    }

                    $title = $parts[2] . '. ';
                    $variant = $parts[2];
                    if (!is_numeric($parts[2])) {
                        $title = $parts[2] . ' ';
                    }

                    $title .= 'Stimme';

                    if (isset($parts[3]) && $parts[3] !== 'pdf') {
                        $title .= ' ' . $parts[3];
                        $variant .= '.' . $parts[3];
                    }

                    $all[] = [
                        'title' => $title,
                        'path' => $variant,
                    ];
                }

                asort($all);
                return $all;
            });
    }

    public static function getSheetDownloadPath(string $sheet, string $instrument, string $variant): string
    {
        return '/' . self::SHEET_FOLDER . '/' . $sheet . '/' . $sheet . '.' . $instrument . '.' . $variant . '.pdf';
    }
}
