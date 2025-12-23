<?php

namespace Tests\Feature;

use App\Models\Instrument;
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

    public function test_index_shows_only_songs_with_sheets_for_user_instrument_groups(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $trumpetInstrument = $trumpet->instruments->first();

        $woodwind = InstrumentGroup::whereTitle('Hohes Holz')->first();
        $fluteInstrument = $woodwind->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $accessibleSong = Song::factory()->create(['title' => 'Accessible Song']);
        Sheet::factory()->create([
            'song_id' => $accessibleSong->id,
            'instrument_id' => $trumpetInstrument->id,
        ]);

        $inaccessibleSong = Song::factory()->create(['title' => 'Inaccessible Song']);
        Sheet::factory()->create([
            'song_id' => $inaccessibleSong->id,
            'instrument_id' => $fluteInstrument->id,
        ]);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee('Accessible Song');
        $response->assertDontSee('Inaccessible Song');
    }

    public function test_index_shows_all_songs_with_permission(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $trumpetInstrument = $trumpet->instruments->first();

        $woodwind = InstrumentGroup::whereTitle('Hohes Holz')->first();
        $fluteInstrument = $woodwind->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        $this->actingAs($user);

        $trumpetSong = Song::factory()->create(['title' => 'Trumpet Song']);
        Sheet::factory()->create([
            'song_id' => $trumpetSong->id,
            'instrument_id' => $trumpetInstrument->id,
        ]);

        $fluteSong = Song::factory()->create(['title' => 'Flute Song']);
        Sheet::factory()->create([
            'song_id' => $fluteSong->id,
            'instrument_id' => $fluteInstrument->id,
        ]);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee('Trumpet Song');
        $response->assertSee('Flute Song');
    }

    public function test_index_shows_empty_state_when_no_sheets(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee(__('No sheets available.'));
    }

    public function test_show_displays_sheets_grouped_by_instrument(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $trumpetInstrument = $trumpet->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Test Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $trumpetInstrument->id,
            'part_number' => 1,
        ]);

        $response = $this->get(route('sheets.show', $song));

        $response->assertStatus(200);
        $response->assertSee($trumpet->title);
        $response->assertSee($trumpetInstrument->title);
        $response->assertSee('1. Stimme');
    }

    public function test_show_only_displays_sheets_for_user_instrument_groups(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $trumpetInstrument = $trumpet->instruments->first();

        $woodwind = InstrumentGroup::whereTitle('Hohes Holz')->first();
        $fluteInstrument = $woodwind->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Shared Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $trumpetInstrument->id,
            'part_number' => 1,
        ]);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $fluteInstrument->id,
            'part_number' => 1,
        ]);

        $response = $this->get(route('sheets.show', $song));

        $response->assertStatus(200);
        $response->assertSee($trumpet->title);
        $response->assertDontSee($woodwind->title);
    }

    public function test_show_displays_all_instruments_with_permission(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $trumpetInstrument = $trumpet->instruments->first();

        $woodwind = InstrumentGroup::whereTitle('Hohes Holz')->first();
        $fluteInstrument = $woodwind->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Shared Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $trumpetInstrument->id,
            'part_number' => 1,
        ]);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $fluteInstrument->id,
            'part_number' => 1,
        ]);

        $response = $this->get(route('sheets.show', $song));

        $response->assertStatus(200);
        $response->assertSee($trumpet->title);
        $response->assertSee($woodwind->title);
    }

    public function test_show_displays_empty_state_when_no_sheets_for_user(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();

        $woodwind = InstrumentGroup::whereTitle('Hohes Holz')->first();
        $fluteInstrument = $woodwind->instruments->first();

        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Flute Only Song']);
        Sheet::factory()->create([
            'song_id' => $song->id,
            'instrument_id' => $fluteInstrument->id,
            'part_number' => 1,
        ]);

        $response = $this->get(route('sheets.show', $song));

        $response->assertStatus(200);
        $response->assertSee(__('No sheets available for your instruments.'));
    }

    public function test_show_has_back_to_song_list_link(): void
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $song = Song::factory()->create(['title' => 'Any Song']);

        $response = $this->get(route('sheets.show', $song));

        $response->assertStatus(200);
        $response->assertSee(__('Back to song list'));
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
