<?php

namespace Tests\Feature;

use App\Models\InstrumentGroup;
use App\Models\Sheet;
use App\Models\Song;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SheetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('sheets');
    }

    public function test_index_shows_only_user_instrument_groups(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee($trumpet->title);

        $otherGroups = InstrumentGroup::where('id', '!=', $trumpet->id)->pluck('title');
        foreach ($otherGroups as $title) {
            $response->assertDontSee($title);
        }
    }

    public function test_index_shows_all_groups_with_permission(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        foreach (InstrumentGroup::all() as $group) {
            $response->assertSee($group->title);
        }
    }

    public function test_show_displays_sheets_for_instrument(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $instrument = $trumpet->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Test Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $instrument->id,
            'part_number' => 1,
        ]);

        $response = $this->get(route('sheets.show', $instrument));

        $response->assertStatus(200);
        $response->assertSee('Test Song');
        $response->assertSee('1. Stimme');
    }

    public function test_show_only_displays_sheets_for_requested_instrument(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $instrument = $trumpet->instruments->first();

        $woodwind = InstrumentGroup::whereTitle('Hohes Holz')->first();
        $fluteInstrument = $woodwind->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Shared Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $instrument->id,
            'part_number' => 1,
        ]);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $fluteInstrument->id,
            'part_number' => 1,
        ]);

        $response = $this->get(route('sheets.show', $instrument));

        $response->assertStatus(200);
        $response->assertSee('Shared Song');
    }

    public function test_download_streams_pdf_file(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $instrument = $trumpet->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'DownloadSong']);
        $sheet = Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $instrument->id,
            'part_number' => 1,
            'file_path' => 'test-song/test.pdf',
        ]);

        Storage::disk('sheets')->put('test-song/test.pdf', 'fake pdf content');

        $response = $this->get(route('sheets.download', $sheet));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
