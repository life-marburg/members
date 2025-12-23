<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SongSets\Pages\CreateSongSet;
use App\Filament\Resources\SongSets\Pages\EditSongSet;
use App\Filament\Resources\SongSets\Pages\ListSongSets;
use App\Filament\Resources\SongSets\RelationManagers\SongsRelationManager;
use App\Filament\Resources\SongSets\SongSetResource;
use App\Models\SongSet;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SongSetResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_can_list_song_sets(): void
    {
        $songSets = SongSet::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(SongSetResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListSongSets::class)
            ->assertCanSeeTableRecords($songSets);
    }

    public function test_can_create_song_set(): void
    {
        $this->actingAs($this->admin)
            ->get(SongSetResource::getUrl('create'))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(CreateSongSet::class)
            ->fillForm([
                'title' => 'Summer Concert 2025',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('song_sets', [
            'title' => 'Summer Concert 2025',
        ]);
    }

    public function test_can_edit_song_set(): void
    {
        $songSet = SongSet::factory()->create();

        $this->actingAs($this->admin)
            ->get(SongSetResource::getUrl('edit', ['record' => $songSet]))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(EditSongSet::class, ['record' => $songSet->id])
            ->fillForm([
                'title' => 'Updated Title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Updated Title', $songSet->fresh()->title);
    }

    public function test_can_delete_song_set(): void
    {
        $songSet = SongSet::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditSongSet::class, ['record' => $songSet->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('song_sets', ['id' => $songSet->id]);
    }

    public function test_edit_page_shows_songs_relation_manager(): void
    {
        $songSet = SongSet::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditSongSet::class, ['record' => $songSet->id])
            ->assertSeeLivewire(SongsRelationManager::class);
    }

    public function test_non_admin_cannot_access_song_sets(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);

        $this->actingAs($user)
            ->get(SongSetResource::getUrl('index'))
            ->assertForbidden();
    }
}
