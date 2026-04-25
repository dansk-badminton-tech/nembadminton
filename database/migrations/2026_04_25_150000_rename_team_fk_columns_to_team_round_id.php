<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('team_receivers', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropUnique(['team_id']);
            $table->renameColumn('team_id', 'team_round_id');
        });

        Schema::table('team_receivers', function (Blueprint $table) {
            $table->foreign('team_round_id')->references('id')->on('team_rounds')->onDelete('cascade');
            $table->unique('team_round_id');
        });

        Schema::table('team_activity_logs', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropIndex(['team_id']);
            $table->renameColumn('team_id', 'team_round_id');
        });

        Schema::table('team_activity_logs', function (Blueprint $table) {
            $table->foreign('team_round_id')->references('id')->on('team_rounds')->onDelete('cascade');
            $table->index('team_round_id');
        });
    }

    public function down(): void
    {
        Schema::table('team_receivers', function (Blueprint $table) {
            $table->dropForeign(['team_round_id']);
            $table->dropUnique(['team_round_id']);
            $table->renameColumn('team_round_id', 'team_id');
        });

        Schema::table('team_receivers', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('team_rounds')->onDelete('cascade');
            $table->unique('team_id');
        });

        Schema::table('team_activity_logs', function (Blueprint $table) {
            $table->dropForeign(['team_round_id']);
            $table->dropIndex(['team_round_id']);
            $table->renameColumn('team_round_id', 'team_id');
        });

        Schema::table('team_activity_logs', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('team_rounds')->onDelete('cascade');
            $table->index('team_id');
        });
    }
};
