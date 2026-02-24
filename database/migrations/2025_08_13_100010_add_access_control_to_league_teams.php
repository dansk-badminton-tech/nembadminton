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
            $table->unsignedBigInteger('clubhouse_id')->nullable()->after('division_id');
            $table->boolean('created_by_system')->default(false)->after('clubhouse_id');
            
            $table->foreign('clubhouse_id')->references('id')->on('clubhouses')->onDelete('cascade');
            $table->index('clubhouse_id');
            $table->index('created_by_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_teams', function (Blueprint $table) {
            $table->dropForeign(['clubhouse_id']);
            $table->dropIndex(['clubhouse_id']);
            $table->dropIndex(['created_by_system']);
            $table->dropColumn(['clubhouse_id', 'created_by_system']);
        });
    }
};
