<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use App\Models\Sheet;
use App\Models\Song;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSheetsFromWebdav extends Command
{
    protected $signature = 'sheets:migrate-from-webdav
                            {--dry-run : Show what would be migrated without doing it}
                            {--skip-existing : Skip sheets that already exist in DB}';

    protected $description = 'Migrate sheet files from WebDAV to local storage and database';

    private const SHEET_FOLDER = 'Life/Noten';

    private int $migrated = 0;
    private int $skipped = 0;
    private int $errors = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $skipExisting = $this->option('skip-existing');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made');
        }

        $this->info('Fetching file list from WebDAV...');

        $structure = $this->getSheetStructureFromWebdav();

        $this->info('Found ' . count($structure) . ' songs');

        $bar = $this->output->createProgressBar(count($structure));
        $bar->start();

        foreach ($structure as $songName => $files) {
            $this->migrateSong($songName, $files, $dryRun, $skipExisting);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration complete!");
        $this->info("Migrated: {$this->migrated}");
        $this->info("Skipped: {$this->skipped}");
        $this->info("Errors: {$this->errors}");

        return Command::SUCCESS;
    }

    private function getSheetStructureFromWebdav(): array
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

    private function migrateSong(string $songName, array $files, bool $dryRun, bool $skipExisting): void
    {
        $song = null;

        if (!$dryRun) {
            $song = Song::firstOrCreate(['title' => $songName]);
        }

        foreach ($files as $filename) {
            $this->migrateFile($song, $songName, $filename, $dryRun, $skipExisting);
        }
    }

    private function migrateFile(?Song $song, string $songName, string $filename, bool $dryRun, bool $skipExisting): void
    {
        // Parse filename: Song.Instrument.Stimme.Variant.pdf
        $parts = explode('.', $filename);

        if (count($parts) < 4) {
            $this->warn("Skipping invalid filename: $filename");
            $this->skipped++;
            return;
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

        // Find matching instrument
        $instrument = $this->findInstrument($instrumentName);

        if (!$instrument) {
            $this->warn("No instrument match for '$instrumentName' in file: $filename");
            $this->skipped++;
            return;
        }

        if ($dryRun) {
            $this->line("Would migrate: $filename -> Song: $songName, Instrument: {$instrument->title}, Part: $partNumber, Variant: $variant");
            $this->migrated++;
            return;
        }

        // Check if already exists
        if ($skipExisting) {
            $exists = Sheet::where('song_id', $song->id)
                ->where('instrument_id', $instrument->id)
                ->where('part_number', $partNumber)
                ->where('variant', $variant)
                ->exists();

            if ($exists) {
                $this->skipped++;
                return;
            }
        }

        // Download file from WebDAV
        $webdavPath = '/' . self::SHEET_FOLDER . '/' . $songName . '/' . $filename;

        try {
            $content = Storage::disk('cloud')->get($webdavPath);
        } catch (\Exception $e) {
            $this->error("Failed to download: $webdavPath - " . $e->getMessage());
            $this->errors++;
            return;
        }

        // Save to local storage
        $localPath = 'song-' . $song->id . '/' . $filename;
        Storage::disk('sheets')->put($localPath, $content);

        // Create database record
        Sheet::create([
            'song_id' => $song->id,
            'instrument_id' => $instrument->id,
            'part_number' => $partNumber,
            'variant' => $variant,
            'file_path' => $localPath,
        ]);

        $this->migrated++;
    }

    private function findInstrument(string $name): ?Instrument
    {
        // Try exact match on file_title first
        $instrument = Instrument::all()->first(function ($inst) use ($name) {
            return $inst->file_title === $name;
        });

        if ($instrument) {
            return $instrument;
        }

        // Try aliases
        return Instrument::all()->first(function ($inst) use ($name) {
            return in_array($name, $inst->aliases ?? []);
        });
    }
}
