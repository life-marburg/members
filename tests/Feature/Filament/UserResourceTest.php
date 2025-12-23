<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\InstrumentGroupsRelationManager;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_admin_can_access_user_list(): void
    {
        $this->actingAs($this->admin);

        $users = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->assertOk()
            ->assertCanSeeTableRecords($users);
    }

    public function test_admin_can_see_status_filter(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListUsers::class)
            ->assertTableFilterExists('status');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $this->actingAs($this->admin);

        $newUser = User::factory()->create(['status' => User::STATUS_NEW]);
        $activeUser = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(ListUsers::class)
            ->filterTable('status', User::STATUS_NEW)
            ->assertCanSeeTableRecords([$newUser])
            ->assertCanNotSeeTableRecords([$activeUser]);
    }

    public function test_admin_can_activate_new_user(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        $newUser = User::factory()->create(['status' => User::STATUS_NEW]);

        Livewire::test(ListUsers::class)
            ->callTableAction('activate', $newUser);

        $this->assertEquals(User::STATUS_UNLOCKED, $newUser->fresh()->status);
        Notification::assertSentTo($newUser, UserStatusChanged::class);
    }

    public function test_admin_can_lock_active_user(): void
    {
        $this->actingAs($this->admin);

        $activeUser = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(ListUsers::class)
            ->callTableAction('lock', $activeUser);

        $this->assertEquals(User::STATUS_LOCKED, $activeUser->fresh()->status);
    }

    public function test_admin_can_unlock_locked_user(): void
    {
        $this->actingAs($this->admin);

        $lockedUser = User::factory()->create(['status' => User::STATUS_LOCKED]);

        Livewire::test(ListUsers::class)
            ->callTableAction('unlock', $lockedUser);

        $this->assertEquals(User::STATUS_UNLOCKED, $lockedUser->fresh()->status);
    }

    public function test_admin_can_edit_user(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->assertOk()
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_edit_page_shows_instrument_groups_relation_manager(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->assertSeeLivewire(InstrumentGroupsRelationManager::class);
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $regularUser = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        $this->actingAs($regularUser)
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_user_with_manage_members_permission_can_access_panel(): void
    {
        $memberManager = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $memberManager->givePermissionTo(Rights::P_MANAGE_MEMBERS);

        $this->actingAs($memberManager)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_send_password_reset_email(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        Livewire::test(ListUsers::class)
            ->callTableAction('sendPasswordReset', $user);

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_admin_can_set_user_password_directly(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $newPassword = 'NewSecurePassword123!';

        Livewire::test(ListUsers::class)
            ->callTableAction('setPassword', $user, [
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]);

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    public function test_set_password_requires_confirmation_match(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $originalPasswordHash = $user->password;

        Livewire::test(ListUsers::class)
            ->callTableAction('setPassword', $user, [
                'new_password' => 'NewSecurePassword123!',
                'new_password_confirmation' => 'DifferentPassword456!',
            ])
            ->assertHasTableActionErrors(['new_password' => 'confirmed']);

        // Password should not have changed
        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }

    public function test_set_password_requires_minimum_length(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $originalPasswordHash = $user->password;

        Livewire::test(ListUsers::class)
            ->callTableAction('setPassword', $user, [
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertHasTableActionErrors(['new_password' => 'min']);

        // Password should not have changed
        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }
}
