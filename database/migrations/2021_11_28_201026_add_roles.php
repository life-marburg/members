<?php

use App\Models\User;
use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = Role::create(['name' => Rights::R_ADMIN]);
        $edit = Permission::create(['name' => Rights::P_EDIT_PAGES]);
        $adminRole->givePermissionTo($edit);

        try {
            $user = User::whereId(1)->firstOrFail();
            $user->assignRole(Rights::R_ADMIN);
        } catch (Exception $e) {
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
}
