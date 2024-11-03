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
        Schema::create('cancellation_publics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('cancellation_collector_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable(); // Corresponding to String, nullable because it's not required
            $table->timestamps();
        });

        Schema::create('cancellation_dates', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('cancellation_public_id')->constrained()->cascadeOnDelete(); // Foreign key
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
        Schema::dropIfExists('cancellation_publics');
    }
};
