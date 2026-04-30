<?php

namespace App\Jobs;

use App\Models\Sheet;
use App\Models\SheetBackup;
use App\Notifications\SheetBackupFailed;
use App\Notifications\SheetBackupReady;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class CreateSheetBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(public int $backupId) {}

    public function handle(): void
    {
        $backup = SheetBackup::find($this->backupId);
        if (! $backup) {
            return;
        }

        $zipAbsPath = null;

        try {
            $backup->update([
                'status' => SheetBackup::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);

            [$relativePath, $fileSize, $sheetCount, $zipAbsPath] = $this->buildZip($backup);

            $backup->update([
                'status' => SheetBackup::STATUS_READY,
                'file_path' => $relativePath,
                'file_size' => $fileSize,
                'sheet_count' => $sheetCount,
                'completed_at' => now(),
            ]);

            $backup->creator?->notify(new SheetBackupReady($backup));
        } catch (Throwable $e) {
            if ($zipAbsPath !== null && file_exists($zipAbsPath)) {
                @unlink($zipAbsPath);
            }

            $backup->update([
                'status' => SheetBackup::STATUS_FAILED,
                'error_message' => substr($e->getMessage(), 0, 500),
                'completed_at' => now(),
            ]);

            $backup->creator?->notify(new SheetBackupFailed($backup));

            throw $e;
        }
    }

    /**
     * Build the ZIP archive for the given backup.
     *
     * @return array{0: string, 1: int, 2: int, 3: string} [relativePath, fileSize, sheetCount, absolutePath]
     */
    protected function buildZip(SheetBackup $backup): array
    {
        $disk = Storage::disk('sheet-backups');
        $sourceDisk = Storage::disk('sheets');

        $relativePath = 'backup-'.$backup->id.'-'.now()->format('Y-m-d-His').'.zip';
        $zipAbsPath = $disk->path($relativePath);

        $dir = dirname($zipAbsPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new ZipArchive;
        $openResult = $zip->open($zipAbsPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            throw new RuntimeException("Failed to open ZIP archive at {$zipAbsPath} (code: {$openResult})");
        }

        $sheetCount = 0;
        $manifestRows = [];

        foreach (Sheet::with(['song', 'instrument'])->lazy() as $sheet) {
            $songTitle = $sheet->song?->title ?? 'unknown';
            $sanitizedSongTitle = str_replace(['/', '\\'], '_', $songTitle);

            $basename = pathinfo($sheet->file_path, PATHINFO_BASENAME);
            if ($basename === '' || $basename === null) {
                $basename = 'sheet-'.$sheet->id.'.pdf';
            }

            $entryName = $sanitizedSongTitle.'/'.$basename;

            $instrumentTitle = $sheet->instrument?->title ?? '';
            $instrumentAliases = $sheet->instrument?->aliases ?? [];
            $instrumentAlias = $instrumentAliases[0] ?? '';

            if (! $sourceDisk->exists($sheet->file_path)) {
                Log::warning('Sheet file missing during backup', [
                    'backup_id' => $backup->id,
                    'sheet_id' => $sheet->id,
                    'file_path' => $sheet->file_path,
                ]);

                $manifestRows[] = [
                    $songTitle,
                    $instrumentTitle,
                    $instrumentAlias,
                    $sheet->part_number,
                    $sheet->variant,
                    'MISSING',
                    $sheet->id,
                ];
                $sheetCount++;

                continue;
            }

            $absPath = $sourceDisk->path($sheet->file_path);
            $zip->addFile($absPath, $entryName);
            $zip->setCompressionName($entryName, ZipArchive::CM_STORE);

            $manifestRows[] = [
                $songTitle,
                $instrumentTitle,
                $instrumentAlias,
                $sheet->part_number,
                $sheet->variant,
                $entryName,
                $sheet->id,
            ];
            $sheetCount++;
        }

        $manifestCsv = $this->buildManifestCsv($manifestRows);
        $zip->addFromString('manifest.csv', $manifestCsv);
        $zip->setCompressionName('manifest.csv', ZipArchive::CM_DEFLATE);

        $zip->close();

        clearstatcache(true, $zipAbsPath);
        $fileSize = filesize($zipAbsPath);
        if ($fileSize === false) {
            throw new RuntimeException("Failed to stat ZIP file at {$zipAbsPath}");
        }

        return [$relativePath, $fileSize, $sheetCount, $zipAbsPath];
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function buildManifestCsv(array $rows): string
    {
        $header = ['song_title', 'instrument_title', 'instrument_alias', 'part_number', 'variant', 'file_path_in_zip', 'sheet_id'];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $header, ',', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '\\');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
