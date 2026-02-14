<?php

use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::create(['name' => Rights::P_MANAGE_SHEETS]);
        $adminRole = Role::whereName(Rights::R_ADMIN)->first();
        $adminRole->givePermissionTo($permission);
    }

    public function down(): void {}
};
