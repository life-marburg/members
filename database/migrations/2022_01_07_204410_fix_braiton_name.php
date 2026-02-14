<?php

use Illuminate\Database\Migrations\Migration;

class FixBraitonName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('instruments')->where('id', 14)->update(['title' => 'Bariton', 'aliases' => ['Baritone']]);
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
