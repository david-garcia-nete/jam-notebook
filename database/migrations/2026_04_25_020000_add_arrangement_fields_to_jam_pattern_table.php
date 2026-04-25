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
        Schema::table('jam_pattern', function (Blueprint $table): void {
            $table->string('section', 50)->nullable()->after('pattern_id');
            $table->integer('position')->default(0)->after('section');
            $table->text('notes')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jam_pattern', function (Blueprint $table): void {
            $table->dropColumn(['section', 'position', 'notes']);
        });
    }
};
