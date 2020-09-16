<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Club extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clubs', static function (Blueprint $table) {
            $table->id();
            $table->string('name1');
            $table->string('name2')->nullable();
            $table->string('address')->nullable();
            $table->integer('zipCode')->nullable();
            $table->string('city', 45)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('memberOf', 45)->nullable();
            $table->string('union', 45)->nullable();
            $table->boolean('hide')->default(0);
            $table->timestamps();
        });
        Schema::create('members', static function (Blueprint $table) {
            $table->id();
            $table->string('refId', 15)->unique()->nullable();
            $table->string('name', 50)->nullable();
            $table->string('gender', 45)->nullable();
            $table->date('birthday')->nullable();
            $table->boolean('hide')->default(0);
            $table->timestamps();
        });
        Schema::create('club_member', static function (Blueprint $table) {
            $table->foreignId('club_id')->constrained();
            $table->foreignId('member_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('club_member');
        Schema::dropIfExists('clubs');
        Schema::dropIfExists('members');
    }
}
