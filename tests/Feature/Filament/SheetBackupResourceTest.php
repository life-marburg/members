<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SheetBackups\Pages\ListSheetBackups;
use App\Filament\Resources\SheetBackups\SheetBackupResource;
use App\Jobs\CreateSheetBackupJob;
use App\Models\SheetBackup;
use App\Models\User;
use App\Rights;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SheetBackupResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_admin_can_access_resource_index(): void
    {
        $this->actingAs($this->admin)
            ->get(SheetBackupResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_non_admin_cannot_access_resource_index(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        $this->actingAs($user)
            ->get(SheetBackupResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_create_action_dispatches_job_and_inserts_pending_row(): void
    {
        Queue::fake();

        Livewire::actingAs($this->admin)
            ->test(ListSheetBackups::class)
            ->callAction('createBackup');

        $this->assertSame(1, SheetBackup::count());

        $backup = SheetBackup::first();
        $this->assertSame(SheetBackup::STATUS_PENDING, $backup->status);
        $this->assertSame($this->admin->id, $backup->created_by);

        Queue::assertPushed(CreateSheetBackupJob::class);
    }

    public function test_create_action_blocked_when_backup_in_progress(): void
    {
        SheetBackup::factory()->inProgress()->create();

        Queue::fake();

        Livewire::actingAs($this->admin)
            ->test(ListSheetBackups::class)
            ->assertActionDisabled('createBackup');

        $this->assertSame(1, SheetBackup::count());
        Queue::assertNotPushed(CreateSheetBackupJob::class);
    }

    public function test_download_action_visible_for_ready_rows_hidden_otherwise(): void
    {
        $readyBackup = SheetBackup::factory()->ready()->create([
            'created_by' => $this->admin->id,
        ]);
        $pendingBackup = SheetBackup::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListSheetBackups::class)
            ->assertActionVisible(TestAction::make('download')->table($readyBackup))
            ->assertActionHidden(TestAction::make('download')->table($pendingBackup));
    }

    public function test_delete_action_removes_row_and_file(): void
    {
        Storage::fake('sheet-backups');

        $backup = SheetBackup::factory()->ready()->create([
            'created_by' => $this->admin->id,
            'file_path' => 'backup-test.zip',
        ]);

        Storage::disk('sheet-backups')->put($backup->file_path, 'fake-zip-contents');
        Storage::disk('sheet-backups')->assertExists($backup->file_path);

        Livewire::actingAs($this->admin)
            ->test(ListSheetBackups::class)
            ->callTableAction('delete', $backup);

        $this->assertDatabaseMissing('sheet_backups', ['id' => $backup->id]);
        Storage::disk('sheet-backups')->assertMissing($backup->file_path);
    }
}
