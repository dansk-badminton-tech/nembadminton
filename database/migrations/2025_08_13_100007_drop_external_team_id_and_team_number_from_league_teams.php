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
        Schema::table('league_teams', function (Blueprint $table) {
            // Drop columns first, which will automatically drop the indexes
            $table->dropColumn(['external_team_id', 'team_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_teams', function (Blueprint $table) {
            $table->integer('team_number')->nullable();
            $table->integer('external_team_id')->nullable();
            
            //$table->index(['club_id', 'team_number']);
            $table->index('external_team_id');
        });
    }
};
