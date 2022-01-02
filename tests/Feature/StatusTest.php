<?php

namespace Tests\Feature;

use App\Http\Livewire\UserUpdateMeta;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_not_allow_login_when_new()
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_NEW]);
        $user->personalData->instrument = 'trumpet';
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('not-yet-active'));
    }

    public function test_should_redirect_active_account_to_dashboard()
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $user->personalData->instrument = 'trumpet';
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_should_not_allow_login_when_locked()
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_NEW]);
        $user->personalData->instrument = 'trumpet';
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('not-yet-active'));
    }

    public function test_should_trigger_notification_when_unlocked()
    {
        Notification::fake();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Rights::R_ADMIN);
        $this->actingAs($admin);
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::test(UserUpdateMeta::class, ['user' => $user])
            ->set('state', [
                'status' => User::STATUS_UNLOCKED,
                'instrument' => 'trumpet',
                'is_admin' => false,
            ])
            ->call('update');

        Notification::assertSentTo($user, UserStatusChanged::class);
    }

    public function test_should_not_trigger_notification_when_locked()
    {
        Notification::fake();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Rights::R_ADMIN);
        $this->actingAs($admin);
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(UserUpdateMeta::class, ['user' => $user])
            ->set('state', [
                'status' => User::STATUS_LOCKED,
                'instrument' => 'trumpet',
                'is_admin' => false,
            ])
            ->call('update');

        Notification::assertNotSentTo($user, UserStatusChanged::class);
    }

    public function test_should_not_trigger_notification_when_status_not_changed()
    {
        Notification::fake();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Rights::R_ADMIN);
        $this->actingAs($admin);
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(UserUpdateMeta::class, ['user' => $user])
            ->set('state', [
                'status' => User::STATUS_UNLOCKED,
                'instrument' => 'trumpet',
                'is_admin' => false,
            ])
            ->call('update');

        Notification::assertNotSentTo($user, UserStatusChanged::class);
    }
}
