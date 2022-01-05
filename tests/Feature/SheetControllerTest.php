<?php

namespace Tests\Feature;

use App\Instruments;
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
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $user->personalData->instrument = 'trumpet';
        $user->personalData->save();
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $response->assertSee(Instruments::INSTRUMENT_GROUPS['trumpet']['instruments']);
        $instruments = collect([]);
        foreach (Instruments::INSTRUMENT_GROUPS as $group => $i) {
            if ($group === 'trumpet') {
                continue;
            }
            $instruments->add($i['instruments']);
        }
        $response->assertDontSee($instruments->flatten()->toArray());
    }

    public function test_should_see_all_sheets()
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $user->personalData->instrument = 'trumpet';
        $user->personalData->save();
        $user->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
        $this->actingAs($user);

        $response = $this->get(route('sheets.index'));

        $response->assertStatus(200);
        $instruments = collect([]);
        foreach (Instruments::INSTRUMENT_GROUPS as $group => $i) {
            $instruments->add($i['instruments']);
        }
        $response->assertSee($instruments->flatten()->toArray());
    }
}
