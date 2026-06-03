<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_rounds', function (Blueprint $table) {
            $table->unsignedInteger('season_id')->nullable();
            $table->foreign('season_id', 'team_rounds_season_id_fk')
                ->references('id')->on('seasons')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('team_rounds', function (Blueprint $table) {
            $table->dropForeign('team_rounds_season_id_fk');
            $table->dropColumn('season_id');
        });
    }
};
