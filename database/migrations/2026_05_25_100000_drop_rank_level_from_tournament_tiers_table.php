<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_tiers', function (Blueprint $table) {
            $table->dropIndex(['rank_level']);
            $table->dropColumn('rank_level');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_tiers', function (Blueprint $table) {
            $table->unsignedTinyInteger('rank_level')->nullable();
            $table->index('rank_level');
        });
    }
};
