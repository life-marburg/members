<?php

use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddAccountDeletionRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $delete = Permission::create(['name' => Rights::P_DELETE_ACCOUNTS]);
        $adminRole = Role::whereName(Rights::R_ADMIN)->first();
        $adminRole->givePermissionTo($delete);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
