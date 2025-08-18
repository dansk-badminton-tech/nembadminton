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
        Schema::create('league_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onDelete('set null');
            $table->integer('team_number')->nullable();
            $table->integer('external_team_id')->nullable();
            $table->timestamps();
            
            $table->index(['club_id', 'team_number']);
            $table->index('external_team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_teams');
    }
};