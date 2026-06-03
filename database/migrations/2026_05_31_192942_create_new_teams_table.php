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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->string('custom_tier_name')->nullable();
            $table->string('group_name')->nullable();
            $table->unsignedBigInteger('clubhouse_id');
            $table->unsignedInteger('season_id');
            $table->timestamps();

            $table->foreign('tier_id')->references('id')->on('tournament_tiers')->nullOnDelete();
            $table->foreign('clubhouse_id', 'teams_clubhouse_id_fk')->references('id')->on('clubhouses')->cascadeOnDelete();
            $table->foreign('season_id', 'teams_season_id_fk')->references('id')->on('seasons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
