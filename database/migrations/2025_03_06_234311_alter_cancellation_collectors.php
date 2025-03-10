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
        Schema::table('cancellation_collectors', function (Blueprint $table) {
            $table->unsignedBigInteger('clubhouse_id')->nullable();
            $table->foreign('clubhouse_id')->references('id')->on('clubhouses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancellation_collectors', function (Blueprint $table) {
            $table->dropForeign(['clubhouse_id']);
            $table->dropColumn('clubhouse_id');
        });
    }
};
