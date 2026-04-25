<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('teams', 'team_rounds');
    }

    public function down(): void
    {
        Schema::rename('team_rounds', 'teams');
    }
};
