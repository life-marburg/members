<?php

namespace Tests\Feature;

use App\Console\Commands\DisableInactiveUsers;
use App\Http\Livewire\UserUpdateMeta;
use App\Models\User;
use App\Notifications\AccountAlmostInactive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class DisableInactiveUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_send_account_almost_inactive_notification()
    {
        Notification::fake();
        /** @var DisableInactiveUsers $cmd */
        $cmd = resolve(DisableInactiveUsers::class);
        $user = User::factory()->create([
            'status' => User::STATUS_UNLOCKED,
            'disable_after_days' => 90,
            'last_active_at' => now()->subDays(88),
        ]);

        $cmd->handle();

        Notification::assertSentTo($user, AccountAlmostInactive::class);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => User::STATUS_UNLOCKED,
        ]);
    }

    public function test_should_disable_account_after_inactive_days_past()
    {
        Notification::fake();
        /** @var DisableInactiveUsers $cmd */
        $cmd = resolve(DisableInactiveUsers::class);
        $user = User::factory()->create([
            'status' => User::STATUS_UNLOCKED,
            'disable_after_days' => 90,
            'last_active_at' => now()->subDays(100),
        ]);

        $cmd->handle();

        Notification::assertNotSentTo($user, AccountAlmostInactive::class);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => User::STATUS_LOCKED,
        ]);
    }

    public function test_should_not_disable_user()
    {
        Notification::fake();
        /** @var DisableInactiveUsers $cmd */
        $cmd = resolve(DisableInactiveUsers::class);
        $user = User::factory()->create([
            'status' => User::STATUS_UNLOCKED,
            'disable_after_days' => null,
            'last_active_at' => now()->subDays(100),
        ]);

        $cmd->handle();

        Notification::assertNotSentTo($user, AccountAlmostInactive::class);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => User::STATUS_UNLOCKED,
        ]);
    }

    public function test_should_set_admin_account_never_expiring()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'status' => User::STATUS_UNLOCKED,
            'disable_after_days' => 90,
            'last_active_at' => now(),
        ]);

        Livewire::test(UserUpdateMeta::class, ['user' => $user])
            ->set('state', [
                'status' => User::STATUS_LOCKED,
                'instrument' => 'trumpet',
                'is_admin' => true,
            ])
            ->call('update');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'disable_after_days' => null,
        ]);
    }
}
