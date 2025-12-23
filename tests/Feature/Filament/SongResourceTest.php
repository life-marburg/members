<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Filament\Resources\Songs\SongResource;
use App\Models\Song;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SongResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_can_create_song_with_is_new_flag(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSong::class)
            ->fillForm([
                'title' => 'New Song',
                'is_new' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('songs', [
            'title' => 'New Song',
            'is_new' => true,
        ]);
    }

    public function test_can_edit_song_is_new_flag(): void
    {
        $song = Song::factory()->create(['is_new' => false]);

        Livewire::actingAs($this->admin)
            ->test(EditSong::class, ['record' => $song->id])
            ->fillForm([
                'is_new' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue((bool) $song->fresh()->is_new);
    }

    public function test_is_new_column_displays_in_table(): void
    {
        Song::factory()->create(['title' => 'Test Song', 'is_new' => true]);

        $this->actingAs($this->admin)
            ->get(SongResource::getUrl('index'))
            ->assertSuccessful();
    }
}
