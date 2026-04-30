<?php

namespace Tests\Feature;

use App\Models\SheetBackup;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SheetBackupDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('sheet-backups');

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);

        $this->user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
    }

    private function signedUrl(SheetBackup $backup): string
    {
        return URL::signedRoute(
            'sheet-backups.download',
            ['backup' => $backup->id],
            now()->addDays(7),
        );
    }

    public function test_admin_with_valid_signed_url_downloads_file(): void
    {
        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'backup-test.zip',
        ]);
        Storage::disk('sheet-backups')->put('backup-test.zip', 'fake zip content');

        $response = $this->actingAs($this->admin)->get($this->signedUrl($backup));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertSame('fake zip content', $response->streamedContent());
    }

    public function test_non_admin_with_valid_signed_url_gets_403(): void
    {
        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'backup-test.zip',
        ]);
        Storage::disk('sheet-backups')->put('backup-test.zip', 'fake zip content');

        $response = $this->actingAs($this->user)->get($this->signedUrl($backup));

        $response->assertStatus(403);
    }

    public function test_guest_with_valid_signed_url_redirects_to_login(): void
    {
        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'backup-test.zip',
        ]);
        Storage::disk('sheet-backups')->put('backup-test.zip', 'fake zip content');

        $response = $this->get($this->signedUrl($backup));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_with_invalid_signature_gets_403(): void
    {
        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'backup-test.zip',
        ]);
        Storage::disk('sheet-backups')->put('backup-test.zip', 'fake zip content');

        $unsignedUrl = route('sheet-backups.download', ['backup' => $backup->id]);

        $response = $this->actingAs($this->admin)->get($unsignedUrl);

        $response->assertStatus(403);
    }

    public function test_admin_with_valid_sig_but_backup_not_ready_gets_404(): void
    {
        $backup = SheetBackup::factory()->create([
            'status' => SheetBackup::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get($this->signedUrl($backup));

        $response->assertStatus(404);
    }

    public function test_admin_with_valid_sig_but_file_missing_gets_404(): void
    {
        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'missing-file.zip',
        ]);

        $response = $this->actingAs($this->admin)->get($this->signedUrl($backup));

        $response->assertStatus(404);
    }

    public function test_download_attempts_are_logged(): void
    {
        $backup = SheetBackup::factory()->ready()->create([
            'file_path' => 'backup-test.zip',
        ]);
        Storage::disk('sheet-backups')->put('backup-test.zip', 'fake zip content');

        Log::spy();

        $response = $this->actingAs($this->admin)->get($this->signedUrl($backup));

        $response->assertStatus(200);

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message, array $context) use ($backup): bool {
                return $message === 'Sheet backup download'
                    && ($context['backup_id'] ?? null) === $backup->id
                    && ($context['user_id'] ?? null) === $this->admin->id;
            })
            ->once();
    }
}
