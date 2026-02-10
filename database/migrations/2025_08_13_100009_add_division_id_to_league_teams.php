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
        Schema::table('league_teams', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('club_id')->constrained('divisions')->onDelete('cascade');
            $table->index('division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_teams', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropIndex(['division_id']);
            $table->dropColumn('division_id');
        });
    }
};
