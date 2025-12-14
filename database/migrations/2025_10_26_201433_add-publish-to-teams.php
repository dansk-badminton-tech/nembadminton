<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('publish')->default(false);
            $table->text('message')->nullable();
        });

        Schema::create('teams_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('teams_id');
            $table->foreign('teams_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->string('action');
            $table->string('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('publish');
            $table->dropColumn('message');
        });

        Schema::dropIfExists('teams_activity_log');
    }
};
