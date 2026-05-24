<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('season_name');
            $table->timestamps();

            $table->unique('season_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
