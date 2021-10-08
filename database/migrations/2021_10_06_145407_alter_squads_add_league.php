<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSquadsAddLeague extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->enum('league', ['LIGA', 'FIRSTDIVISION', 'OTHER'])->default('OTHER');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->dropColumn('league');
        });
    }
}
