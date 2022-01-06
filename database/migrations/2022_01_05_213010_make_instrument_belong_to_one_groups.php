<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeInstrumentBelongToOneGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->foreignId('instrument_group_id')->nullable()->constrained();
        });

        $rels = DB::table('instrument_groups_instrument')
            ->get()
            ->mapWithKeys(fn($item, $key) => [$item->instrument_id => $item->instrument_group_id])
            ->toArray();
        $instruments = DB::table('instruments')->get();

        foreach ($instruments as $instrument) {
            DB::table('instruments')
                ->where('id', $instrument->id)
                ->update(['instrument_group_id' => $rels[$instrument->id]]);
        }

        Schema::drop('instrument_groups_instrument');
        Schema::table('instruments', function (Blueprint $table) {
            $table->foreignId('instrument_group_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
