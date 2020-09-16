<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Points extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points', static function (Blueprint $table) {
            $table->id();
            $table->integer('points')->nullable();
            $table->integer('position')->nullable();
            $table->string('cll')->nullable();
            $table->string('clh')->nullable();
            $table->string('category', 10)->nullable();
            $table->string('vintage', 10)->nullable();
            $table->foreignId('member_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('points');
    }
}
