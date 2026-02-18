<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_folders', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->foreignId('group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_folders');
    }
};
