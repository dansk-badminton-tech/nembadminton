<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancellation_collector_clubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cancellation_collector_id');
            $table->unsignedBigInteger('club_id');
            $table->timestamps();
            $table->foreign('cancellation_collector_id')->references('id')->on('cancellation_collectors')->onDelete('cascade');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->unique(['cancellation_collector_id', 'club_id'], 'cancellation_collector_clubs_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cancellation_collector_clubs');
    }
};
