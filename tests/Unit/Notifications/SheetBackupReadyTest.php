<?php

namespace Tests\Unit\Notifications;

use App\Models\SheetBackup;
use App\Models\User;
use App\Notifications\SheetBackupReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class SheetBackupReadyTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_mail_returns_mail_message_with_subject_line_and_action(): void
    {
        $user = User::factory()->create();
        $backup = SheetBackup::factory()->ready()->create(['created_by' => $user->id]);

        $notification = new SheetBackupReady($backup);
        $mail = $notification->toMail($user);

        $this->assertSame(__('Your sheet backup is ready'), $mail->subject);
        $this->assertTrue(Str::contains($mail->actionUrl, '/sheet-backups/'.$backup->id.'/download'));
        $this->assertTrue(Str::contains($mail->actionUrl, 'signature='));
    }

    public function test_action_url_passes_has_valid_signature(): void
    {
        $user = User::factory()->create();
        $backup = SheetBackup::factory()->ready()->create(['created_by' => $user->id]);

        $notification = new SheetBackupReady($backup);
        $mail = $notification->toMail($user);

        $request = Request::create($mail->actionUrl);

        $this->assertTrue($request->hasValidSignature());
    }

    public function test_via_returns_mail_channel(): void
    {
        $user = User::factory()->create();
        $backup = SheetBackup::factory()->ready()->create(['created_by' => $user->id]);

        $this->assertSame(['mail'], (new SheetBackupReady($backup))->via($user));
    }
}
