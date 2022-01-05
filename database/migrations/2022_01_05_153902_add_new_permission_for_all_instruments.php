<?php

use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class AddNewPermissionForAllInstruments extends Migration
{
    public function up()
    {
        $see = Permission::create(['name' => Rights::P_VIEW_ALL_INSTRUMENTS]);
        $adminRole = Role::whereName(Rights::R_ADMIN)->first();
        $adminRole->givePermissionTo($see);
    }

    public function down()
    {
    }
}
