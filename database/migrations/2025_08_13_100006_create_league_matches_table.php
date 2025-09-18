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
        Schema::create('league_matches', function (Blueprint $table) {
            $table->id();
            $table->integer('external_match_id')->unique();
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade');
            $table->foreignId('age_group_id')->constrained('age_groups')->onDelete('cascade');
            $table->foreignId('team1_id')->constrained('league_teams')->onDelete('cascade');
            $table->foreignId('team2_id')->constrained('league_teams')->onDelete('cascade');
            $table->foreignId('venue_id')->nullable()->constrained('venues')->onDelete('set null');
            $table->foreignId('season_id')->constrained('seasons')->onDelete('cascade');
            $table->dateTime('match_time')->nullable();
            $table->integer('score1')->nullable();
            $table->integer('score2')->nullable();
            $table->timestamps();
            
            $table->index('external_match_id');
            $table->index(['season_id', 'division_id']);
            $table->index(['team1_id', 'team2_id']);
            $table->index('match_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_matches');
    }
};