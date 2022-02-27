<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartitionInstrumentGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $id = DB::table('instrument_groups')
            ->insertGetId([
                'title' => 'Partitur',
                'is_user_selectable' => false,
            ]);
        DB::table('instruments')
            ->insert([
                'title' => 'Partitur',
                'instrument_group_id' => $id,
            ]);
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
