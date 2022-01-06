<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAliasesForInstruments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->json('aliases')->nullable();
        });

        DB::table('instruments')->where('id', 21)->update(['aliases' => [
            'PiccoloFloete',
            'Piccolo',
        ]]);
        DB::table('instruments')->where('id', 1)->update(['aliases' => [
            'Floete',
        ]]);
        DB::table('instruments')->where('id', 19)->update(['aliases' => [
            'Guitar',
            'E-Gitarre',
            'EGitarre',
            'EGuitar',
            'BassGitarre',
            'BassGuitar',
        ]]);
        DB::table('instruments')->where('id', 17)->update(['aliases' => [
            'Schlagzeug',
            'Percussion',
        ]]);
        DB::table('instruments')->where('id', 26)->update(['aliases' => [
            'Mallet',
            'Mallets',
        ]]);
        DB::table('instruments')->where('id', 13)->update(['aliases' => [
            'SoloPosaune',
        ]]);
        DB::table('instruments')->where('id', 9)->update(['aliases' => [
            'SoloTrompete',
        ]]);
        DB::table('instruments')->where('id', 11)->update(['aliases' => [
            'Fluegelhorn',
            'Flügelhorn',
        ]]);
        DB::table('instruments')->where('id', 27)->update(['aliases' => [
            'RingBells',
            'Bells',
            'Carillon',
        ]]);
        DB::table('instruments')->where('id', 28)->update(['aliases' => [
            'Timpani',
        ]]);
        DB::table('instruments')->where('id', 29)->update(['aliases' => [
            'Choir',
            'Gesang',
        ]]);
        DB::table('instruments')->where('id', 12)->update(['aliases' => [
            'SoloHorn',
            'Bariton',
        ]]);
        DB::table('instruments')->where('id', 10)->update(['aliases' => [
            'SoloCornett',
        ]]);
        DB::table('instruments')->where('id', 32)->update(['aliases' => [
            'Keyboards',
        ]]);
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
