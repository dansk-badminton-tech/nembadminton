<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->unsignedInteger('season_id');
            $table->string('group_name');
            // Keep migration immutable: do not reference app enums here.
            $table->enum('phase_type', [
                'REGULAR_SEASON',
                'PROMOTION_RELEGATION',
                'PLAYOFF',
                'BYES_PAPER_TEAM',
                'OTHER',
            ])->default('OTHER');
            $table->timestamps();

            $table->foreign('tier_id')->references('id')->on('tournament_tiers')->nullOnDelete();
            $table->foreign('season_id')->references('id')->on('seasons')->cascadeOnDelete();

            $table->unique(['season_id', 'tier_id', 'group_name']);
            $table->index('phase_type');
            $table->index('season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_groups');
    }
};
