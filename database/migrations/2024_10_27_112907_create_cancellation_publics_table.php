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
        Schema::create('cancellation_dates', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('cancellation_id')->constrained()->cascadeOnDelete(); // Foreign key
            $table->date('date'); // Date field
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_dates');
    }
};
