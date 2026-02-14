<?php

use App\Models\User;
use App\Rights;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AddLastActiveAtToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('last_active_at')->nullable();
            $table->integer('disable_after_days')->nullable();
        });

        DB::table('users')->update(['disable_after_days' => 90]);

        /** @var User[] $admins */
        $admins = Role::findByName(Rights::R_ADMIN, 'web')->users()->get();
        foreach ($admins as $admin) {
            $admin->disable_after_days = null;
            $admin->save();
        }
    }

    public function down() {}
}
