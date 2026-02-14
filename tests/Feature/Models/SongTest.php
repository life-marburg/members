<?php

namespace Tests\Feature\Models;

use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongTest extends TestCase
{
    use RefreshDatabase;

    public function test_song_has_many_sheets(): void
    {
        $song = Song::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $song->sheets);
    }

    public function test_song_can_be_created_with_title(): void
    {
        $song = Song::create(['title' => 'Test Song']);

        $this->assertDatabaseHas('songs', ['title' => 'Test Song']);
    }
}
