<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
