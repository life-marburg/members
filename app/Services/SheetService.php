<?php

namespace App\Services;

use App\Models\Instrument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SheetService
{
    private const SHEET_FOLDER = 'Life/Noten';
    private const CACHE_KEY = 'sheet_files';

    protected function getSheetStructureFromWebdav(): array
    {
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
    }

    protected function getSongFileStructure(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn() => $this->getSheetStructureFromWebdav());
    }

    public function refreshSheetsCache()
    {
        Cache::put(self::CACHE_KEY, $this->getSheetStructureFromWebdav());
    }

    public function getSheetsForInstrument(?Instrument $instrument): ?Collection
    {
        if ($instrument === null) {
            return null;
        }

        $structure = collect($this->getSongFileStructure());

        return $structure
            ->map(function ($item) use ($instrument) {
                $all = [];
                foreach ($item as $it) {
                    foreach ($instrument->title_with_alias as $title) {
                        if (str_contains($it, '.'.$title.'.')) {
                            $all[] = $it;
                        }
                    }
                }

                return collect($all)->unique()->toArray();
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
                        'instrument' => $parts[1],
                    ];
                }

                asort($all);
                return $all;
            });
    }

    public static function getSheetDownloadPath(string $sheet, string $instrumentFileName, string $variant): string
    {
        return '/' . self::SHEET_FOLDER . '/' . $sheet . '/' . $sheet . '.' . $instrumentFileName . '.' . $variant . '.pdf';
    }
}
