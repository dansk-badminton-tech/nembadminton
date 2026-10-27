<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->string('match_kind')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->dropColumn('match_kind');
        });
    }
};
