<?php

namespace Tests\Unit\Models;

use App\Models\SheetBackup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SheetBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_creator(): void
    {
        $user = User::factory()->create();
        $backup = SheetBackup::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $backup->creator);
        $this->assertEquals($user->id, $backup->creator->id);
    }

    public function test_factory_creates_pending_row_by_default(): void
    {
        $backup = SheetBackup::factory()->create();

        $this->assertSame(SheetBackup::STATUS_PENDING, $backup->status);
        $this->assertNull($backup->file_path);
        $this->assertNull($backup->file_size);
    }

    public function test_ready_state_fills_completion_fields(): void
    {
        $backup = SheetBackup::factory()->ready()->create();

        $this->assertSame(SheetBackup::STATUS_READY, $backup->status);
        $this->assertNotNull($backup->file_path);
        $this->assertNotNull($backup->file_size);
        $this->assertNotNull($backup->sheet_count);
        $this->assertNotNull($backup->started_at);
        $this->assertNotNull($backup->completed_at);
    }

    public function test_casts_apply(): void
    {
        SheetBackup::factory()->ready()->create();

        $backup = SheetBackup::query()->firstOrFail();

        $this->assertInstanceOf(Carbon::class, $backup->started_at);
        $this->assertInstanceOf(Carbon::class, $backup->completed_at);
        $this->assertIsInt($backup->file_size);
        $this->assertIsInt($backup->sheet_count);
    }
}
