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
        Schema::table('squads', function (Blueprint $table) {
            $table->unsignedInteger('external_team_fight_id')->nullable();
            $table->string('name')->nullable();
            $table->dateTime('playing_datetime')->nullable();
            $table->string('playing_place')->nullable();
            $table->string('playing_address')->nullable();
            $table->unsignedInteger('playing_zip_code')->nullable();
            $table->string('playing_city')->nullable();
        });

        Schema::table('teams', function(Blueprint $table){
            $table->unsignedInteger('round')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function(Blueprint $table){
            $table->dropColumn('round');
        });

        Schema::table('squads', function (Blueprint $table) {
            $table->dropColumn('external_team_fight_id');
            $table->dropColumn('name');
            $table->dropColumn('playing_datetime');
            $table->dropColumn('playing_place');
            $table->dropColumn('playing_address');
            $table->dropColumn('playing_zip_code');
            $table->dropColumn('playing_city');
        });
    }
};
