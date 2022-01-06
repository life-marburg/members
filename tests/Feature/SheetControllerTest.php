<?php

namespace Tests\Feature;

use App\Instruments;
use App\Models\Instrument;
use App\Models\InstrumentGroup;
use App\Models\User;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SheetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_only_see_own_sheets()
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        /** @var User $user */
        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee($trumpet->instruments->map(fn($i) => $i->title)->toArray());
        $instruments = Instrument::where('instrument_group_id', '!=', $trumpet->id)
            ->get()
            ->map(fn($i) => $i->title)
            ->toArray();
        $response->assertDontSee($instruments);
    }

    public function test_should_see_all_sheets()
    {
        $trumpet = InstrumentGroup::whereTitle('Trompete')->first();
        /** @var User $user */
        $user = User::factory()->create();
        $user->instrumentGroups()->attach($trumpet->id);
        $user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee(Instrument::all()->map(fn($i) => $i->title)->toArray());
    }
}
