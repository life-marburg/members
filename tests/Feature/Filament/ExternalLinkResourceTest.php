<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ExternalLinks\ExternalLinkResource;
use App\Filament\Resources\ExternalLinks\Pages\CreateExternalLink;
use App\Filament\Resources\ExternalLinks\Pages\EditExternalLink;
use App\Filament\Resources\ExternalLinks\Pages\ListExternalLinks;
use App\Models\ExternalLink;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExternalLinkResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);

        // Clear seeded data from migration for isolated tests
        ExternalLink::query()->delete();
    }

    public function test_can_list_external_links(): void
    {
        $links = ExternalLink::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(ExternalLinkResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListExternalLinks::class)
            ->assertCanSeeTableRecords($links);
    }

    public function test_can_create_external_link(): void
    {
        $this->actingAs($this->admin)
            ->get(ExternalLinkResource::getUrl('create'))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(CreateExternalLink::class)
            ->fillForm([
                'title' => 'New Link',
                'url' => 'https://newlink.com',
                'target' => '_blank',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('external_links', [
            'title' => 'New Link',
            'url' => 'https://newlink.com',
        ]);
    }

    public function test_can_edit_external_link(): void
    {
        $link = ExternalLink::factory()->create();

        $this->actingAs($this->admin)
            ->get(ExternalLinkResource::getUrl('edit', ['record' => $link]))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(EditExternalLink::class, ['record' => $link->id])
            ->fillForm([
                'title' => 'Updated Title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Updated Title', $link->fresh()->title);
    }

    public function test_can_delete_external_link(): void
    {
        $link = ExternalLink::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditExternalLink::class, ['record' => $link->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('external_links', ['id' => $link->id]);
    }
}
