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
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('playable')->default(true);
        });

        Schema::table('cancellations', function (Blueprint $table) {
            $table->foreignId('cancellation_collector_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('message')->nullable(); // Corresponding to String, nullable because it's not required
            $table->text('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('playable');
        });
        Schema::table('cancellations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancellation_collector_id');
            $table->dropColumn('message');
            $table->dropColumn('created_by');
        });
    }
};
