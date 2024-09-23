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
        Schema::table('cancellations', static function (Blueprint $table) {
            $table->boolean('permanent')->default(false);
            $table->integer('round')->nullable();
            $table->integer('season')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cancellations', static function (Blueprint $table) {
            $table->dropColumn('permanent');
            $table->dropColumn('round');
            $table->dropColumn('season');
        });
    }
};
