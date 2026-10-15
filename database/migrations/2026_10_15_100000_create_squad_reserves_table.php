<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A reserve record (§43): on a Squad, the reserve from a lower Squad plays for the replaced player. Scoped per
        // Scenario like category slots; null is the lineup of a TeamRound without an official Scenario. Names and
        // genders are kept so a record still reads well after the lineup changes under it.
        Schema::create('squad_reserves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('squad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_round_scenario_id')->nullable()->constrained('team_round_scenarios')->cascadeOnDelete();
            $table->string('reserve_ref_id');
            $table->string('reserve_name');
            $table->string('reserve_gender');
            $table->string('replaced_ref_id');
            $table->string('replaced_name');
            $table->string('replaced_gender');
            $table->timestamps();

            $table->index(['squad_id', 'team_round_scenario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('squad_reserves');
    }
};
