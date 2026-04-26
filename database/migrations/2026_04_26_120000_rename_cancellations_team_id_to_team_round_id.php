<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cancellations', function (Blueprint $table) {
            $table->renameColumn('teamId', 'team_round_id');
        });
    }

    public function down(): void
    {
        Schema::table('cancellations', function (Blueprint $table) {
            $table->renameColumn('team_round_id', 'teamId');
        });
    }
};
