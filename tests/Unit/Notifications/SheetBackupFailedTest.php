<?php

namespace Tests\Unit\Notifications;

use App\Models\SheetBackup;
use App\Models\User;
use App\Notifications\SheetBackupFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SheetBackupFailedTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_mail_includes_error_message_and_no_action(): void
    {
        $user = User::factory()->create();
        $backup = SheetBackup::factory()->failed()->create([
            'created_by' => $user->id,
            'error_message' => 'something went wrong',
        ]);

        $notification = new SheetBackupFailed($backup);
        $mail = $notification->toMail($user);

        $this->assertSame(__('Your sheet backup failed'), $mail->subject);
        $this->assertNull($mail->actionText);

        $combinedLines = collect($mail->introLines)
            ->merge($mail->outroLines)
            ->implode(' ');

        $this->assertStringContainsString('something went wrong', $combinedLines);
    }

    public function test_via_returns_mail_channel(): void
    {
        $user = User::factory()->create();
        $backup = SheetBackup::factory()->failed()->create(['created_by' => $user->id]);

        $this->assertSame(['mail'], (new SheetBackupFailed($backup))->via($user));
    }
}
