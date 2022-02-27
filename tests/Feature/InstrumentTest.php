<?php

namespace Tests\Feature;

use App\Models\InstrumentGroup;
use App\Models\User;
use App\Notifications\UserIsWaitingForActivation;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InstrumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_account_must_set_instrument()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('set-instrument.form'));
    }

    public function test_new_account_set_invalid_instrument()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('set-instrument.save'), ['instrument' => '999999']);

        $response->assertSessionHasErrors();
    }

    public function test_should_not_redirect_inactive_accounts_with_instrument_to_dashboard()
    {
        /** @var User $user */
        $user = User::factory()->newAccount()->create();
        $user->instrumentGroups()->attach(1);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('not-yet-active'));
    }

    public function test_new_user_instrument_set_should_work()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $instrument = 1;

        $response = $this->post(route('set-instrument.save'), [
            'instrument' => $instrument,
        ]);
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('user_instrument_group', [
            'user_id' => $user->id,
            'instrument_group_id' => $instrument,
        ]);
    }

    public function test_should_only_see_selectable_instruments()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $instrumentGroups = InstrumentGroup::all();

        $response = $this->get(route('set-instrument.form'));
        foreach ($instrumentGroups as $instrumentGroup) {
            if($instrumentGroup->is_user_selectable) {
                $response->assertSee($instrumentGroup->title);
            } else {
                $response->assertDontSee($instrumentGroup->title);
            }
        }
    }

    public function test_should_not_select_not_selectable_instrument()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $instrument = InstrumentGroup::whereIsUserSelectable(false)->first();

        $response = $this->post(route('set-instrument.save'), [
            'instrument' => $instrument->id,
        ]);
        $response->assertSessionHasErrors();
    }
}
