<?php

namespace Tests\Feature;

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

        $response = $this->post(route('set-instrument.save'), ['instrument' => 'not-an-instrument']);

        $response->assertSessionHasErrors();
    }

    public function test_account_with_instrument()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->personalData->instrument = 'trumpet';
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_new_user_instrument_set_should_trigger_notification()
    {
        Notification::fake();
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $instrument = 'trumpet';
        /** @var User $admin1 */
        $admin1 = User::factory()->create();
        $admin1->assignRole(Rights::R_ADMIN);
        /** @var User $admin2 */
        $admin2 = User::factory()->create();
        $admin2->assignRole(Rights::R_ADMIN);

        $response = $this->post(route('set-instrument.save'), [
            'instrument' => $instrument,
        ]);
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('personal_data', [
            'user_id' => $user->id,
            'instrument' => $instrument,
        ]);
        Notification::assertSentTo([$admin1, $admin2], UserIsWaitingForActivation::class);
    }
}
