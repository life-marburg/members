<?php

namespace Database\Seeders;

use App\Models\User;
use App\Rights;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Rights::R_ADMIN);

        User::factory(25)->create();
        User::factory(10)->newAccount()->create();
    }
}
