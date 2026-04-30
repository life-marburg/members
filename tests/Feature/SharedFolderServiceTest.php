<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\SharedFolder;
use App\Models\User;
use App\Services\SharedFolderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedFolderServiceTest extends TestCase
{
    use RefreshDatabase;

    private SharedFolderService $service;

    private User $user;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SharedFolderService;
        $this->user = User::factory()->create();
        $this->group = Group::factory()->create();
        $this->user->groups()->attach($this->group);
    }

    public function test_unshared_folder_is_not_accessible(): void
    {
        $this->assertFalse($this->service->canAccess($this->user, 'secret'));
    }

    public function test_shared_folder_is_accessible_to_group_member(): void
    {
        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);

        $this->assertTrue($this->service->canAccess($this->user, 'docs'));
    }

    public function test_shared_folder_is_not_accessible_to_non_member(): void
    {
        $otherGroup = Group::factory()->create();
        SharedFolder::create(['path' => 'docs', 'group_id' => $otherGroup->id]);

        $this->assertFalse($this->service->canAccess($this->user, 'docs'));
    }

    public function test_subfolder_inherits_parent_access(): void
    {
        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);

        $this->assertTrue($this->service->canAccess($this->user, 'docs/sub'));
        $this->assertTrue($this->service->canAccess($this->user, 'docs/sub/deep'));
    }

    public function test_subfolder_override_replaces_parent(): void
    {
        $otherGroup = Group::factory()->create();
        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);
        SharedFolder::create(['path' => 'docs/restricted', 'group_id' => $otherGroup->id]);

        $this->assertTrue($this->service->canAccess($this->user, 'docs'));
        $this->assertFalse($this->service->canAccess($this->user, 'docs/restricted'));
        $this->assertFalse($this->service->canAccess($this->user, 'docs/restricted/deep'));
    }

    public function test_null_group_blocks_access(): void
    {
        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);
        SharedFolder::create(['path' => 'docs/private', 'group_id' => null]);

        $this->assertTrue($this->service->canAccess($this->user, 'docs'));
        $this->assertFalse($this->service->canAccess($this->user, 'docs/private'));
        $this->assertFalse($this->service->canAccess($this->user, 'docs/private/deep'));
    }

    public function test_multiple_groups_on_same_folder(): void
    {
        $groupA = Group::factory()->create();
        $groupB = Group::factory()->create();
        $this->user->groups()->attach($groupB);

        SharedFolder::create(['path' => 'docs', 'group_id' => $groupA->id]);
        SharedFolder::create(['path' => 'docs', 'group_id' => $groupB->id]);

        $this->assertTrue($this->service->canAccess($this->user, 'docs'));
    }

    public function test_get_accessible_root_folders(): void
    {
        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);
        SharedFolder::create(['path' => 'photos', 'group_id' => $this->group->id]);

        $otherGroup = Group::factory()->create();
        SharedFolder::create(['path' => 'admin-only', 'group_id' => $otherGroup->id]);

        $roots = $this->service->getAccessibleRootPaths($this->user);

        $this->assertContains('docs', $roots);
        $this->assertContains('photos', $roots);
        $this->assertNotContains('admin-only', $roots);
    }

    public function test_filter_items_removes_inaccessible_subfolders(): void
    {
        $otherGroup = Group::factory()->create();
        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);
        SharedFolder::create(['path' => 'docs/restricted', 'group_id' => $otherGroup->id]);

        $paths = ['docs/readme.txt', 'docs/public', 'docs/restricted'];
        $filtered = $this->service->filterAccessiblePaths($this->user, 'docs', $paths);

        $this->assertContains('docs/readme.txt', $filtered);
        $this->assertContains('docs/public', $filtered);
        $this->assertNotContains('docs/restricted', $filtered);
    }

    public function test_public_folder_is_accessible_to_user_without_groups(): void
    {
        $loner = User::factory()->create();
        SharedFolder::create(['path' => 'docs', 'group_id' => null, 'is_public' => true]);

        $this->assertTrue($this->service->canAccess($loner, 'docs'));
        $this->assertTrue($this->service->canAccess($loner, 'docs/sub'));
    }

    public function test_public_folder_is_accessible_to_user_with_unrelated_groups(): void
    {
        $otherGroup = Group::factory()->create();
        $stranger = User::factory()->create();
        $stranger->groups()->attach($otherGroup);

        SharedFolder::create(['path' => 'docs', 'group_id' => null, 'is_public' => true]);

        $this->assertTrue($this->service->canAccess($stranger, 'docs'));
    }

    public function test_block_on_child_overrides_public_parent(): void
    {
        SharedFolder::create(['path' => 'docs', 'group_id' => null, 'is_public' => true]);
        SharedFolder::create(['path' => 'docs/private', 'group_id' => null]);

        $this->assertTrue($this->service->canAccess($this->user, 'docs'));
        $this->assertFalse($this->service->canAccess($this->user, 'docs/private'));
        $this->assertFalse($this->service->canAccess($this->user, 'docs/private/deep'));
    }

    public function test_public_child_overrides_unrelated_parent_share(): void
    {
        $otherGroup = Group::factory()->create();
        SharedFolder::create(['path' => 'docs', 'group_id' => $otherGroup->id]);
        SharedFolder::create(['path' => 'docs/open', 'group_id' => null, 'is_public' => true]);

        $stranger = User::factory()->create();

        $this->assertFalse($this->service->canAccess($stranger, 'docs'));
        $this->assertTrue($this->service->canAccess($stranger, 'docs/open'));
        $this->assertTrue($this->service->canAccess($stranger, 'docs/open/deep'));
    }

    public function test_public_roots_are_returned_for_user_without_groups(): void
    {
        $loner = User::factory()->create();
        SharedFolder::create(['path' => 'public-docs', 'group_id' => null, 'is_public' => true]);
        SharedFolder::create(['path' => 'group-only', 'group_id' => $this->group->id]);

        $roots = $this->service->getAccessibleRootPaths($loner);

        $this->assertContains('public-docs', $roots);
        $this->assertNotContains('group-only', $roots);
    }

    public function test_filter_admits_public_child_override(): void
    {
        $otherGroup = Group::factory()->create();
        $stranger = User::factory()->create();

        SharedFolder::create(['path' => 'docs', 'group_id' => $otherGroup->id]);
        SharedFolder::create(['path' => 'docs/open', 'group_id' => null, 'is_public' => true]);

        $paths = ['docs/secret', 'docs/open'];
        $filtered = $this->service->filterAccessiblePaths($stranger, 'docs', $paths);

        $this->assertContains('docs/open', $filtered);
        $this->assertContains('docs/secret', $filtered);
    }

    public function test_can_navigate_walks_into_folder_with_only_public_subfolder(): void
    {
        $loner = User::factory()->create();
        SharedFolder::create(['path' => 'top/inner', 'group_id' => null, 'is_public' => true]);

        $this->assertTrue($this->service->canNavigate($loner, 'top'));
    }
}
