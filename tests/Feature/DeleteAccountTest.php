<?php

namespace Tests\Feature;

use App\Livewire\AdminDeleteUserForm;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accounts_can_be_deleted()
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(DeleteUserForm::class)
            ->set('password', 'dev')
            ->call('deleteUser');

        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_before_account_can_be_deleted()
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(DeleteUserForm::class)
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }

    public function test_should_require_admin_permission_to_delete_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Rights::R_ADMIN);
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(AdminDeleteUserForm::class, ['user' => $user])
            ->set('password', 'dev')
            ->call('deleteUser');

        $this->assertNull($user->fresh());
    }

    public function test_should_not_allow_deletion_without_admin_permissions()
    {
        /** @var User $notAnAdmin */
        $notAnAdmin = User::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($notAnAdmin);

        Livewire::test(AdminDeleteUserForm::class, ['user' => $user])
            ->set('password', 'dev')
            ->call('deleteUser');

        $this->assertNotNull($user->fresh());
    }
}
