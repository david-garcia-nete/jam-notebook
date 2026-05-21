<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->text('tablature')->nullable()->after('content');
            $table->string('notation_url', 2048)->nullable()->after('tablature');
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->dropColumn(['tablature', 'notation_url']);
        });
    }
};
