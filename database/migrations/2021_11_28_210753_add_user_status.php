<?php

use App\Models\User;
use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddUserStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('status')->default(0);
        });

        DB::table('users')->update(['status' => User::STATUS_UNLOCKED]);

        $adminRole = Role::whereName(Rights::R_ADMIN)->first();
        $edit = Permission::create(['name' => Rights::P_MANAGE_MEMBERS]);
        $adminRole->givePermissionTo($edit);
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
