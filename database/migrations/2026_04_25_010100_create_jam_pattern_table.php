<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jam_pattern', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pattern_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['jam_id', 'pattern_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_pattern');
    }
};
