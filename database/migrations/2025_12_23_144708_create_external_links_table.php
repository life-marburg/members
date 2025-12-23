<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url');
            $table->boolean('show_external_icon')->default(true);
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('target', ['_self', '_blank'])->default('_blank');
            $table->timestamps();
        });

        // Seed with existing hardcoded link
        DB::table('external_links')->insert([
            'title' => 'Back to Homepage',
            'url' => 'https://life-marburg.de/',
            'show_external_icon' => true,
            'position' => 0,
            'is_active' => true,
            'target' => '_blank',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
