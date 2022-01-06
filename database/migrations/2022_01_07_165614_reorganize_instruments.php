<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReorganizeInstruments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('instruments')->where('id', 12)->update(['aliases' => [
            'SoloHorn',
        ]]);
        DB::table('instruments')->insert(['title' => 'Percussion', 'instrument_group_id' => 7]);
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
