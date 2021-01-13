<?php
declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('squads', function (Blueprint $table) {
            $table->id();
            $table->integer('playerLimit');
            $table->string('teams_id');
            $table->foreign('teams_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('squad_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['MD', 'DS', 'HS', 'DD', 'HD']);
            $table->string('name', 10);
            $table->foreignId('squad_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('squad_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gender');
            $table->string('member_ref_id');
            $table->foreignId('squad_category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('squad_members', function (Blueprint $table) {
            $table->foreign('member_ref_id')->references('refId')->on('members');
        });

        Schema::create('squad_points', function (Blueprint $table) {
            $table->id();
            $table->integer('points')->nullable();
            $table->string('category', 4)->nullable();
            $table->integer('position')->nullable();
            $table->foreignId('squad_member_id')->constrained()->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('squads');
        Schema::dropIfExists('squad_categories');
        Schema::dropIfExists('squad_members');
        Schema::dropIfExists('squad_points');
        Schema::enableForeignKeyConstraints();
    }
}
