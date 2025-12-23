<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_sets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('song_set_song', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['song_set_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_set_song');
        Schema::dropIfExists('song_sets');
    }
};
