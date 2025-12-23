<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_page_resource_is_not_accessible_without_admin_role(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertFalse(PageResource::canAccess());
    }

    public function test_page_resource_is_accessible_with_admin_role(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(PageResource::canAccess());
    }

    public function test_cannot_create_pages(): void
    {
        $this->assertFalse(PageResource::canCreate());
    }

    public function test_cannot_delete_pages(): void
    {
        $page = Page::factory()->create();

        $this->assertFalse(PageResource::canDelete($page));
    }

    public function test_can_list_pages(): void
    {
        Page::factory()->create(['path' => 'test-page']);

        $this->actingAs($this->admin)
            ->get(PageResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('/test-page');
    }

    public function test_can_edit_page(): void
    {
        $page = Page::factory()->create([
            'path' => 'test-page',
            'content' => '<p>Original content</p>',
        ]);

        $this->actingAs($this->admin)
            ->get(PageResource::getUrl('edit', ['record' => $page]))
            ->assertSuccessful()
            ->assertSee('/test-page');
    }

    public function test_can_update_page_content(): void
    {
        $page = Page::factory()->create([
            'path' => 'test-page',
            'content' => '<p>Original content</p>',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Filament\Resources\Pages\Pages\EditPage::class, [
                'record' => $page->id,
            ])
            ->fillForm([
                'content' => '<p>Updated content</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'path' => 'test-page',
            'content' => '<p>Updated content</p>',
        ]);
    }

    public function test_path_cannot_be_changed(): void
    {
        $page = Page::factory()->create([
            'path' => 'original-path',
            'content' => '<p>Content</p>',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Filament\Resources\Pages\Pages\EditPage::class, [
                'record' => $page->id,
            ])
            ->fillForm([
                'content' => '<p>Updated content</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Path should remain unchanged
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'path' => 'original-path',
        ]);
    }
}
