<?php

namespace Database\Factories;

use App\Models\PersonalData;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('dev'), // password
            'remember_token' => Str::random(10),
            'status' => User::STATUS_UNLOCKED,
            'last_active_at' => $this->faker->dateTimeBetween('-90 days'),
            'disable_after_days' => 90,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            PersonalData::whereUserId($user->id)->delete();
            PersonalData::factory()->create(['user_id' => $user->id]);
            /*
             * Looks nice, but still creates garbage data:
             *
             $data = PersonalData::factory()->make();
            $data->user()->associate($user);
            $data->save();
            */
        });
    }

    public function unverified(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function newAccount(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => User::STATUS_NEW,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
    public function withPersonalTeam()
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(function (array $attributes, User $user) {
                    return ['name' => $user->name.'\'s Team', 'user_id' => $user->id, 'personal_team' => true];
                }),
            'ownedTeams'
        );
    }
}
