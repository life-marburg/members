<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CreateSheetBackupJob;
use App\Models\Instrument;
use App\Models\InstrumentGroup;
use App\Models\Sheet;
use App\Models\SheetBackup;
use App\Models\Song;
use App\Models\User;
use App\Notifications\SheetBackupFailed;
use App\Notifications\SheetBackupReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CreateSheetBackupJobTest extends TestCase
{
    use RefreshDatabase;

    private Instrument $instrument;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('sheets');
        Storage::fake('sheet-backups');
        Notification::fake();

        $group = new InstrumentGroup;
        $group->forceFill(['title' => 'Backup Test Group'])->save();

        $this->instrument = new Instrument;
        $this->instrument->forceFill([
            'title' => 'Backup Test Trumpet',
            'aliases' => ['Trp', 'Trompete'],
            'instrument_group_id' => $group->id,
        ])->save();

        $this->admin = User::factory()->create();
    }

    public function test_builds_zip_with_sheets_and_manifest(): void
    {
        $songA = Song::factory()->create(['title' => 'Song Alpha']);
        $songB = Song::factory()->create(['title' => 'Song Beta']);

        $sheets = collect([
            Sheet::factory()->create([
                'song_id' => $songA->id,
                'instrument_id' => $this->instrument->id,
                'part_number' => 1,
                'variant' => null,
                'file_path' => 'song-alpha/alpha-1.pdf',
            ]),
            Sheet::factory()->create([
                'song_id' => $songA->id,
                'instrument_id' => $this->instrument->id,
                'part_number' => 2,
                'variant' => null,
                'file_path' => 'song-alpha/alpha-2.pdf',
            ]),
            Sheet::factory()->create([
                'song_id' => $songB->id,
                'instrument_id' => $this->instrument->id,
                'part_number' => 1,
                'variant' => 'B',
                'file_path' => 'song-beta/beta-1B.pdf',
            ]),
        ]);

        foreach ($sheets as $sheet) {
            Storage::disk('sheets')->put($sheet->file_path, 'fake-pdf-content-'.$sheet->id);
        }

        $backup = SheetBackup::factory()->create(['created_by' => $this->admin->id]);

        (new CreateSheetBackupJob($backup->id))->handle();

        $backup->refresh();

        $this->assertSame(SheetBackup::STATUS_READY, $backup->status);
        $this->assertSame(3, $backup->sheet_count);
        $this->assertGreaterThan(0, $backup->file_size);
        $this->assertNotNull($backup->started_at);
        $this->assertNotNull($backup->completed_at);
        $this->assertNotNull($backup->file_path);

        $zipPath = Storage::disk('sheet-backups')->path($backup->file_path);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath) === true);
        $this->assertSame(4, $zip->numFiles, 'ZIP should contain 3 sheets + manifest.csv');

        $manifest = $zip->getFromName('manifest.csv');
        $this->assertNotFalse($manifest);

        $lines = array_values(array_filter(explode("\n", $manifest), fn ($line) => $line !== ''));
        $this->assertSame(
            'song_title,instrument_title,instrument_alias,part_number,variant,file_path_in_zip,sheet_id',
            $lines[0]
        );
        $this->assertCount(4, $lines, 'Manifest should have header + 3 rows');

        $manifestBody = implode("\n", array_slice($lines, 1));
        $this->assertStringContainsString('Song Alpha', $manifestBody);
        $this->assertStringContainsString('Song Beta', $manifestBody);
        $this->assertStringContainsString('Backup Test Trumpet', $manifestBody);
        $this->assertStringContainsString('Trp', $manifestBody);

        $hasAlphaFolder = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'Song Alpha/')) {
                $hasAlphaFolder = true;
                break;
            }
        }
        $this->assertTrue($hasAlphaFolder, 'ZIP should contain entries under "Song Alpha/" folder');

        $zip->close();
    }

    public function test_dispatches_ready_notification_on_success(): void
    {
        $song = Song::factory()->create(['title' => 'Notif Song']);
        $sheet = Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $this->instrument->id,
            'file_path' => 'notif/sheet.pdf',
        ]);
        Storage::disk('sheets')->put($sheet->file_path, 'fake-pdf');

        $backup = SheetBackup::factory()->create(['created_by' => $this->admin->id]);

        (new CreateSheetBackupJob($backup->id))->handle();

        Notification::assertSentTo($this->admin, SheetBackupReady::class);
        Notification::assertNotSentTo($this->admin, SheetBackupFailed::class);
    }

    public function test_handles_missing_file_gracefully(): void
    {
        $song = Song::factory()->create(['title' => 'Missing Song']);

        $existingSheet = Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $this->instrument->id,
            'file_path' => 'present/sheet.pdf',
        ]);
        Storage::disk('sheets')->put($existingSheet->file_path, 'present');

        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $this->instrument->id,
            'file_path' => 'absent/sheet.pdf',
        ]);

        $backup = SheetBackup::factory()->create(['created_by' => $this->admin->id]);

        (new CreateSheetBackupJob($backup->id))->handle();

        $backup->refresh();

        $this->assertSame(SheetBackup::STATUS_READY, $backup->status);
        $this->assertSame(2, $backup->sheet_count);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('sheet-backups')->path($backup->file_path)) === true);

        $manifest = $zip->getFromName('manifest.csv');
        $this->assertNotFalse($manifest);
        $this->assertStringContainsString('MISSING', $manifest);

        $zip->close();
    }

    public function test_marks_failed_and_dispatches_failed_notification_when_zip_build_fails(): void
    {
        $song = Song::factory()->create(['title' => 'Failure Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $this->instrument->id,
            'file_path' => 'fail/sheet.pdf',
        ]);

        $backup = SheetBackup::factory()->create(['created_by' => $this->admin->id]);

        $job = new class($backup->id) extends CreateSheetBackupJob
        {
            protected function buildZip(SheetBackup $backup): array
            {
                throw new \RuntimeException('boom');
            }
        };

        try {
            $job->handle();
            $this->fail('Expected RuntimeException to be re-thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $backup->refresh();

        $this->assertSame(SheetBackup::STATUS_FAILED, $backup->status);
        $this->assertSame('boom', $backup->error_message);
        $this->assertNotNull($backup->completed_at);

        Notification::assertSentTo($this->admin, SheetBackupFailed::class);
        Notification::assertNotSentTo($this->admin, SheetBackupReady::class);
    }

    public function test_silently_returns_when_backup_row_missing(): void
    {
        (new CreateSheetBackupJob(99999))->handle();

        Notification::assertNothingSent();
    }

    public function test_tries_is_one(): void
    {
        $this->assertSame(1, (new CreateSheetBackupJob(1))->tries);
    }
}
