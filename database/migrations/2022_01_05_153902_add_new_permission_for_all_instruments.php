<?php

use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddNewPermissionForAllInstruments extends Migration
{
    public function up()
    {
        $see = Permission::create(['name' => Rights::P_VIEW_ALL_INSTRUMENTS]);
        $adminRole = Role::whereName(Rights::R_ADMIN)->first();
        $adminRole->givePermissionTo($see);
    }

    public function down() {}
}
