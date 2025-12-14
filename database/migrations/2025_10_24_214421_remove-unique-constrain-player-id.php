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
        Schema::table('users', function (Blueprint $table) {
            // Drops the unique index created earlier: "users_player_id_unique"
            $table->dropUnique('users_player_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restores the unique constraint on player_id
            $table->unique('player_id', 'users_player_id_unique');
        });
    }

};
