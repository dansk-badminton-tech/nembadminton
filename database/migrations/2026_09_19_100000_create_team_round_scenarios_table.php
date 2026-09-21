<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_round_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('team_round_id', 24);
            $table->string('name');
            $table->boolean('is_official')->default(false);
            $table->timestamps();

            $table->foreign('team_round_id')
                ->references('id')
                ->on('team_rounds')
                ->cascadeOnDelete();

            $table->index(['team_round_id', 'is_official'], 'trs_round_official_idx');
        });

        Schema::table('squad_categories', function (Blueprint $table) {
            $table->foreignId('team_round_scenario_id')
                ->nullable()
                ->after('squad_id')
                ->constrained('team_round_scenarios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('squad_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_round_scenario_id');
        });

        Schema::dropIfExists('team_round_scenarios');
    }
};
