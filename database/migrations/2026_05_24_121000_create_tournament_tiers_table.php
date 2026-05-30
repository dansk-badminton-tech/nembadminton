<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('tier_name')->nullable();
            $table->unsignedTinyInteger('rank_level')->nullable();
            $table->timestamps();

            $table->unique('tier_name');
            $table->index('rank_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_tiers');
    }
};
