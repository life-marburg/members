<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\SharedFiles;
use App\Models\Group;
use App\Models\SharedFolder;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SharedFilesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_add_share_creates_public_row_when_is_public_is_set(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs')
            ->set('isPublic', true)
            ->call('addShare');

        $this->assertDatabaseHas('shared_folders', [
            'path' => 'docs',
            'group_id' => null,
            'is_public' => true,
        ]);
    }

    public function test_add_share_creates_group_row_when_is_public_is_false(): void
    {
        $this->actingAs($this->admin);
        $group = Group::factory()->create();

        Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs')
            ->set('selectedGroupId', $group->id)
            ->call('addShare');

        $this->assertDatabaseHas('shared_folders', [
            'path' => 'docs',
            'group_id' => $group->id,
            'is_public' => false,
        ]);
    }

    public function test_add_share_does_nothing_without_group_or_public_flag(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs')
            ->call('addShare');

        $this->assertDatabaseMissing('shared_folders', ['path' => 'docs']);
    }

    public function test_add_share_resets_form_state(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs')
            ->set('isPublic', true)
            ->call('addShare')
            ->assertSet('isPublic', false)
            ->assertSet('selectedGroupId', null);
    }

    public function test_open_share_dialog_resets_is_public(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SharedFiles::class)
            ->set('isPublic', true)
            ->call('openShareDialog', 'docs')
            ->assertSet('isPublic', false);
    }

    public function test_shares_property_marks_public_rows(): void
    {
        $this->actingAs($this->admin);
        SharedFolder::create(['path' => 'docs', 'group_id' => null, 'is_public' => true]);

        $component = Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs');

        $shares = $component->instance()->shares;

        $this->assertCount(1, $shares);
        $this->assertTrue($shares[0]['is_public']);
        $this->assertSame(__('Everyone'), $shares[0]['group_name']);
    }

    public function test_inherited_share_info_includes_public_rows(): void
    {
        $this->actingAs($this->admin);
        SharedFolder::create(['path' => 'docs', 'group_id' => null, 'is_public' => true]);

        $component = Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs/sub');

        $inherited = $component->instance()->inheritedShareInfo;

        $this->assertNotNull($inherited);
        $this->assertSame('docs', $inherited['path']);
        $this->assertContains(__('Everyone'), $inherited['groups']);
    }

    public function test_has_public_share_is_true_when_folder_is_shared_publicly(): void
    {
        $this->actingAs($this->admin);
        SharedFolder::create(['path' => 'docs', 'group_id' => null, 'is_public' => true]);

        $component = Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs');

        $this->assertTrue($component->instance()->hasPublicShare);
    }

    public function test_has_public_share_is_false_when_only_group_shares_exist(): void
    {
        $this->actingAs($this->admin);
        $group = Group::factory()->create();
        SharedFolder::create(['path' => 'docs', 'group_id' => $group->id]);

        $component = Livewire::test(SharedFiles::class)
            ->call('openShareDialog', 'docs');

        $this->assertFalse($component->instance()->hasPublicShare);
    }
}
