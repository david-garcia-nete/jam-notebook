<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->text('embed_code')->nullable()->after('notation_url');
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->dropColumn('embed_code');
        });
    }
};
