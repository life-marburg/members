<?php

namespace Tests\Feature;

use App\Livewire\UpdatePersonalDataForm;
use App\Models\User;
use App\Notifications\UserIsWaitingForActivation;
use App\Rights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire;
use Tests\TestCase;

class PersonalDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin1;
    private User $admin2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::factory()->create();
        $this->admin1->assignRole(Rights::R_ADMIN);
        $this->admin2 = User::factory()->create();
        $this->admin2->assignRole(Rights::R_ADMIN);
    }

    public function test_new_personal_data_set_should_trigger_notification()
    {
        Notification::fake();
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::test(UpdatePersonalDataForm::class, [
            'user' => $user,
            'notifyAdmin' => true,
            'redirectAfterSave' => 'dashboard'
        ])
            ->set('state', [
                'name' => 'Lorem Ipsum',
                'street' => 'Examplestreet 42',
                'city' => 'Testcity',
                'zip' => '12345',
                'mobile_phone' => '0123456789',
            ])
            ->call('update')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('personal_data', [
            'user_id' => $user->id,
            'street' => 'Examplestreet 42',
            'city' => 'Testcity',
            'zip' => '12345',
            'mobile_phone' => '0123456789',
        ]);

        Notification::assertSentTo([$this->admin1, $this->admin2], UserIsWaitingForActivation::class);
    }

    public function test_personal_data_set_should_not_trigger_notification_by_default()
    {
        Notification::fake();
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::test(UpdatePersonalDataForm::class, [
            'user' => $user,
        ])
            ->set('state', [
                'name' => 'Lorem Ipsum',
                'street' => 'Examplestreet 42',
                'city' => 'Testcity',
                'zip' => '12345',
                'mobile_phone' => '0123456789',
            ])
            ->call('update');

        $this->assertDatabaseHas('personal_data', [
            'user_id' => $user->id,
            'street' => 'Examplestreet 42',
            'city' => 'Testcity',
            'zip' => '12345',
            'mobile_phone' => '0123456789',
        ]);

        Notification::assertNotSentTo([$this->admin1, $this->admin2], UserIsWaitingForActivation::class);
    }
}
