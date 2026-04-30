<?php

namespace App\Console\Commands;

use App\Models\SheetBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupSheetBackups extends Command
{
    protected $signature = 'sheet-backups:cleanup';

    protected $description = 'Delete SheetBackup rows older than 7 days and orphan files on the sheet-backups disk.';

    public function handle(): int
    {
        $disk = Storage::disk('sheet-backups');

        $deletedRows = 0;
        $deletedFiles = 0;
        $deletedOrphans = 0;

        $expiredBackups = SheetBackup::query()
            ->where('created_at', '<', now()->subDays(7))
            ->get();

        foreach ($expiredBackups as $backup) {
            if ($backup->file_path !== null && $disk->exists($backup->file_path)) {
                $disk->delete($backup->file_path);
                $deletedFiles++;
            }

            Log::info('Deleted expired sheet backup', [
                'backup_id' => $backup->id,
                'file_path' => $backup->file_path,
            ]);

            $backup->delete();
            $deletedRows++;
        }

        foreach ($disk->files() as $file) {
            if (! SheetBackup::query()->where('file_path', $file)->exists()) {
                $disk->delete($file);
                $deletedOrphans++;

                Log::info('Deleted orphan sheet backup file', [
                    'file' => $file,
                ]);
            }
        }

        $this->info(sprintf(
            'Cleaned up %d expired backup row(s), %d associated file(s), and %d orphan file(s).',
            $deletedRows,
            $deletedFiles,
            $deletedOrphans,
        ));

        return Command::SUCCESS;
    }
}
