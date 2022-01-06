<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixInstrumentNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('instruments')->insert(['title' => 'Piccolo Flöte', 'instrument_group_id' => 1]);

        DB::table('instruments')->where('id', 6)->update(['title' => 'Alt Sax']);
        DB::table('instruments')->where('id', 7)->update(['title' => 'Tenor Sax']);
        DB::table('instruments')->where('id', 8)->update(['title' => 'Bariton Sax']);
        DB::table('instruments')->insert(['title' => 'Es Klarinette', 'instrument_group_id' => 2]);
        DB::table('instruments')->insert(['title' => 'Alt Klarinette', 'instrument_group_id' => 2]);

        DB::table('instruments')->where('id', 10)->update(['title' => 'Cornett']);

        DB::table('instruments')->insert(['title' => 'Tenor Horn', 'instrument_group_id' => 5]);

        DB::table('instruments')->insert(['title' => 'Bass Posaune', 'instrument_group_id' => 6]);
        DB::table('instruments')->where('id', 14)->update(['title' => 'Baritone']);

        DB::table('instruments')->insert(['title' => 'Xylophon', 'instrument_group_id' => 7]);
        DB::table('instruments')->insert(['title' => 'Glockenspiel', 'instrument_group_id' => 7]);
        DB::table('instruments')->insert(['title' => 'Pauken', 'instrument_group_id' => 7]);
        DB::table('instruments')->insert(['title' => 'Vibraphone', 'instrument_group_id' => 7]);

        DB::table('instruments')->insert(['title' => 'Chor', 'instrument_group_id' => 8]);
        DB::table('instruments')->insert(['title' => 'Synthesizer', 'instrument_group_id' => 8]);
        DB::table('instruments')->insert(['title' => 'Streichbass', 'instrument_group_id' => 8]);
        DB::table('instruments')->insert(['title' => 'Keyboard', 'instrument_group_id' => 8]);
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
