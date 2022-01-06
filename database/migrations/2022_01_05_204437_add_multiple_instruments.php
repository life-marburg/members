<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMultipleInstruments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('instrument_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('instrument_groups_instrument', function (Blueprint $table) {
            $table->foreignId('instrument_id')->constrained();
            $table->foreignId('instrument_group_id')->constrained();
        });

        $groups = [
            'woodwind' => [
                'name' => 'Hohes Holz',
                'instruments' => ['Flöte', 'Oboe', 'Fagott'],
            ],
            'clarinet' => [
                'name' => 'Klarinette',
                'instruments' => ['Klarinette', 'Bass Klarinette'],
            ],
            'sax' => [
                'name' => 'Sax',
                'instruments' => ['Alt', 'Tenor', 'Bariton'],
            ],
            'trumpet' => [
                'name' => 'Trompete',
                'instruments' => ['Trompete', 'Cornette', 'Flügelhorn'],
            ],
            'horn' => [
                'name' => 'Horn',
                'instruments' => ['Horn'],
            ],
            'brass' => [
                'name' => 'Tiefes Blech',
                'instruments' => ['Posaune', 'Bariton', 'Euphonium', 'Tuba'],
            ],
            'drums' => [
                'name' => 'Schlagwerk',
                'instruments' => ['Schlagwerk'],
            ],
            'other' => [
                'name' => 'Sonstiges',
                'instruments' => ['Klavier', 'Gitarre', 'E-Bass'],
            ],
        ];

        $groupIds = [];

        foreach ($groups as $key => $group) {
            $gr = DB::table('instrument_groups')->insertGetId(['title' => $group['name']]);
            $groupIds[$key] = $gr;

            $instruments = [];
            foreach ($group['instruments'] as $in) {
                $instruments[] = DB::table('instruments')->insertGetId(['title' => $in]);
            }

            foreach ($instruments as $instrument) {
                DB::table('instrument_groups_instrument')->insert([
                    'instrument_id' => $instrument,
                    'instrument_group_id' => $gr,
                ]);
            }
        }

        Schema::create('user_instrument_group', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('instrument_group_id')->constrained();
        });

        $d = DB::table('personal_data')->get();
        foreach ($d as $data) {
            DB::table('user_instrument_group')->insert([
                'user_id' => $data->user_id,
                'instrument_group_id' => $groupIds[$data->instrument],
            ]);
        }

        Schema::table('personal_data', function (Blueprint $table) {
            $table->dropColumn('instrument');
        });
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
