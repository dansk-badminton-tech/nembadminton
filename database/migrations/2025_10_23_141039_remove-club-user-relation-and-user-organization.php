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
        Schema::dropColumns('users', 'organization_id');
        Schema::drop('club_user');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate club_user table if it was dropped
        if (!Schema::hasTable('club_user')) {
            Schema::create('club_user', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('club_id');
            });
        }

        // Re-add organization_id column to users if it was dropped
        if (!Schema::hasColumn('users', 'organization_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Use nullable to avoid issues when restoring on non-empty tables
                $table->unsignedBigInteger('organization_id')->nullable();
            });
        }
    }
};
