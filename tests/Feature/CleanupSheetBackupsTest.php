<?php

namespace Tests\Feature;

use App\Console\Commands\CleanupSheetBackups;
use App\Models\SheetBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class CleanupSheetBackupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_row_older_than_7_days_and_its_file(): void
    {
        Storage::fake('sheet-backups');
        Storage::disk('sheet-backups')->put('old.zip', 'old data');

        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'old.zip',
            'created_at' => now()->subDays(8),
        ]);

        $this->artisan(CleanupSheetBackups::class);

        $this->assertDatabaseMissing('sheet_backups', ['id' => $backup->id]);
        Storage::disk('sheet-backups')->assertMissing('old.zip');
    }

    public function test_keeps_row_younger_than_7_days(): void
    {
        Storage::fake('sheet-backups');
        Storage::disk('sheet-backups')->put('recent.zip', 'recent data');

        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'recent.zip',
            'created_at' => now()->subDays(6),
        ]);

        $this->artisan(CleanupSheetBackups::class);

        $this->assertDatabaseHas('sheet_backups', ['id' => $backup->id]);
        Storage::disk('sheet-backups')->assertExists('recent.zip');
    }

    public function test_handles_missing_file_for_old_row(): void
    {
        Storage::fake('sheet-backups');

        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'gone.zip',
            'created_at' => now()->subDays(10),
        ]);

        $this->artisan(CleanupSheetBackups::class);

        $this->assertDatabaseMissing('sheet_backups', ['id' => $backup->id]);
    }

    public function test_removes_ghost_file_with_no_db_row(): void
    {
        Storage::fake('sheet-backups');
        Storage::disk('sheet-backups')->put('ghost.zip', 'ghost data');

        $this->artisan(CleanupSheetBackups::class);

        Storage::disk('sheet-backups')->assertMissing('ghost.zip');
    }

    public function test_keeps_file_referenced_by_recent_row(): void
    {
        Storage::fake('sheet-backups');
        Storage::disk('sheet-backups')->put('recent.zip', 'recent data');

        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'recent.zip',
            'created_at' => now()->subDays(2),
        ]);

        $this->artisan(CleanupSheetBackups::class);

        $this->assertDatabaseHas('sheet_backups', ['id' => $backup->id]);
        Storage::disk('sheet-backups')->assertExists('recent.zip');
    }

    public function test_logs_deletions(): void
    {
        Storage::fake('sheet-backups');
        Storage::disk('sheet-backups')->put('old.zip', 'old data');

        SheetBackup::factory()->ready()->create([
            'file_path' => 'old.zip',
            'created_at' => now()->subDays(9),
        ]);

        Log::spy();

        $this->artisan(CleanupSheetBackups::class);

        Log::shouldHaveReceived('info')
            ->with('Deleted expired sheet backup', Mockery::on(fn (array $context): bool => ($context['file_path'] ?? null) === 'old.zip'))
            ->once();
    }
}
