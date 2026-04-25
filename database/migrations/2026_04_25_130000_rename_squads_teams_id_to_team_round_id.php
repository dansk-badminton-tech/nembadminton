<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->dropForeign(['teams_id']);
            $table->renameColumn('teams_id', 'team_round_id');
        });

        Schema::table('squads', function (Blueprint $table) {
            $table->foreign('team_round_id')->references('id')->on('team_rounds')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->dropForeign(['team_round_id']);
            $table->renameColumn('team_round_id', 'teams_id');
        });

        Schema::table('squads', function (Blueprint $table) {
            $table->foreign('teams_id')->references('id')->on('team_rounds')->cascadeOnDelete();
        });
    }
};
