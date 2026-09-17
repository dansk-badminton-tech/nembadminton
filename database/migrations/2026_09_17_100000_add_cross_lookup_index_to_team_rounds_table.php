<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_rounds', function (Blueprint $table) {
            $table->index(['clubhouse_id', 'season_id', 'round'], 'team_rounds_cross_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('team_rounds', function (Blueprint $table) {
            $table->dropIndex('team_rounds_cross_lookup_idx');
        });
    }
};
