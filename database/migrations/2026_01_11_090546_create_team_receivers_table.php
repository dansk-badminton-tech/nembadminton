<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_receivers', function (Blueprint $table) {
            $table->id();
            $table->string('team_id', 24)->unique();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->json('emails');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_receivers');
    }
};
