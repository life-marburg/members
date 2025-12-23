<?php

namespace Tests\Unit\Models;

use App\Models\Song;
use App\Models\SongSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongSetTest extends TestCase
{
    use RefreshDatabase;

    public function test_song_set_has_title(): void
    {
        $songSet = SongSet::factory()->create(['title' => 'Summer Concert 2025']);

        $this->assertEquals('Summer Concert 2025', $songSet->title);
    }

    public function test_song_set_belongs_to_many_songs(): void
    {
        $songSet = SongSet::factory()->create();
        $songs = Song::factory()->count(3)->create();

        $songSet->songs()->attach($songs->pluck('id'));

        $this->assertCount(3, $songSet->songs);
    }

    public function test_songs_are_ordered_by_position(): void
    {
        $songSet = SongSet::factory()->create();
        $song1 = Song::factory()->create(['title' => 'First']);
        $song2 = Song::factory()->create(['title' => 'Second']);
        $song3 = Song::factory()->create(['title' => 'Third']);

        $songSet->songs()->attach($song1->id, ['position' => 2]);
        $songSet->songs()->attach($song2->id, ['position' => 0]);
        $songSet->songs()->attach($song3->id, ['position' => 1]);

        $orderedSongs = $songSet->songs;

        $this->assertEquals('Second', $orderedSongs[0]->title);
        $this->assertEquals('Third', $orderedSongs[1]->title);
        $this->assertEquals('First', $orderedSongs[2]->title);
    }

    public function test_pivot_has_position(): void
    {
        $songSet = SongSet::factory()->create();
        $song = Song::factory()->create();

        $songSet->songs()->attach($song->id, ['position' => 5]);

        $this->assertEquals(5, $songSet->songs->first()->pivot->position);
    }
}
