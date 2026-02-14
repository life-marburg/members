<?php

use Illuminate\Database\Migrations\Migration;

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
