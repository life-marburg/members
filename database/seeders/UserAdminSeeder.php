<?php

namespace Database\Seeders;

use App\Models\User;
use App\Rights;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAdminSeeder extends Seeder
{
    // Makes the first user admin
    public function run()
    {
        $user = User::whereId(1)->firstOrFail();
        $user->assignRole(Rights::R_ADMIN);

        DB::table('users')->update(['status' => User::STATUS_UNLOCKED]);
    }
}
