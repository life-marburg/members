<?php

namespace App\Services;

use App\Models\Instrument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SheetService
{
    public const SHEET_FOLDER = 'Life/Noten';
    private const CACHE_KEY = 'sheet_files';

    public function getSheetStructureFromWebdav(): array
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
                        if (str_contains($it, '.' . $title . '.')) {
                            $all[] = $it;
                        }
                    }
                }

                return collect($all)->unique()->toArray();
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
            })
            ->filter(function ($item) {
                return count($item) > 0;
            });
    }

    public static function getSheetDownloadPath(string $sheet, string $instrumentFileName, string $variant): string
    {
        return '/' . self::SHEET_FOLDER . '/' . $sheet . '/' . $sheet . '.' . $instrumentFileName . '.' . $variant . '.pdf';
    }

    /**
     * Parse a sheet filename into its components.
     *
     * Expected format: Song.Instrument.Part.Variant.pdf
     *
     * @return array{instrument: string, part_number: int|null, variant: string|null}|null
     */
    public function parseSheetFilename(string $filename): ?array
    {
        $parts = explode('.', $filename);

        if (count($parts) < 4) {
            return null;
        }

        $instrumentName = $parts[1];
        $partNumber = is_numeric($parts[2]) ? (int) $parts[2] : null;
        $variant = null;

        if (isset($parts[3]) && $parts[3] !== 'pdf') {
            $variant = $parts[3];
        }

        // Non-numeric part becomes variant
        if (!is_numeric($parts[2])) {
            $variant = $parts[2];
            if (isset($parts[3]) && $parts[3] !== 'pdf') {
                $variant .= ' ' . $parts[3];
            }
        }

        return [
            'instrument' => $instrumentName,
            'part_number' => $partNumber,
            'variant' => $variant,
        ];
    }

    /**
     * Find an instrument by its filename representation (file_title or alias).
     */
    public function findInstrumentByFilename(string $name): ?Instrument
    {
        return Instrument::all()->first(function (Instrument $instrument) use ($name) {
            return in_array($name, $instrument->title_with_alias, true);
        });
    }
}
