<?php

use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::create(['name' => Rights::P_MANAGE_GROUPS]);
        $adminRole = Role::whereName(Rights::R_ADMIN)->first();
        $adminRole?->givePermissionTo($permission);
    }

    public function down(): void
    {
        Permission::whereName(Rights::P_MANAGE_GROUPS)->delete();
    }
};
