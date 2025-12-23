<?php

namespace App\Console\Commands;

use App\Models\Sheet;
use App\Models\Song;
use App\Services\SheetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateSheetsFromWebdav extends Command
{
    protected $signature = 'sheets:migrate-from-webdav
                            {--dry-run : Show what would be migrated without doing it}
                            {--skip-existing : Skip sheets that already exist in DB}';

    protected $description = 'Migrate sheet files from WebDAV to local storage and database';

    private int $migrated = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private array $invalidFiles = [];

    public function __construct(private SheetService $sheetService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $skipExisting = $this->option('skip-existing');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made');
        }

        $this->info('Fetching file list from WebDAV...');

        $structure = $this->sheetService->getSheetStructureFromWebdav();

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

        if (count($this->invalidFiles) > 0) {
            $this->newLine();
            $this->warn("Invalid files that could not be processed:");
            foreach ($this->invalidFiles as $file) {
                $this->line("  - {$file['path']} ({$file['reason']})");
            }
        }

        return Command::SUCCESS;
    }

    private function migrateSong(string $songName, array $files, bool $dryRun, bool $skipExisting): void
    {
        $song = null;
        $songTitle = Str::headline($songName);

        if (!$dryRun) {
            $song = Song::firstOrCreate(['title' => $songTitle]);
        }

        foreach ($files as $filename) {
            $this->migrateFile($song, $songName, $filename, $dryRun, $skipExisting);
        }
    }

    private function migrateFile(?Song $song, string $songName, string $filename, bool $dryRun, bool $skipExisting): void
    {
        $fullPath = SheetService::SHEET_FOLDER . '/' . $songName . '/' . $filename;

        $parsed = $this->sheetService->parseSheetFilename($filename);

        if ($parsed === null) {
            $this->invalidFiles[] = ['path' => $fullPath, 'reason' => 'invalid filename format'];
            $this->skipped++;
            return;
        }

        $instrument = $this->sheetService->findInstrumentByFilename($parsed['instrument']);

        if (!$instrument) {
            $this->invalidFiles[] = ['path' => $fullPath, 'reason' => "no instrument match for '{$parsed['instrument']}'"];
            $this->skipped++;
            return;
        }

        $partNumber = $parsed['part_number'];
        $variant = $parsed['variant'];

        if ($dryRun) {
            $songTitle = Str::headline($songName);
            $this->line("Would migrate: $filename -> Song: $songTitle, Instrument: {$instrument->title}, Part: $partNumber, Variant: $variant");
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
        try {
            $content = Storage::disk('cloud')->get('/' . $fullPath);
        } catch (\Exception $e) {
            $this->invalidFiles[] = ['path' => $fullPath, 'reason' => 'download failed: ' . $e->getMessage()];
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
}
