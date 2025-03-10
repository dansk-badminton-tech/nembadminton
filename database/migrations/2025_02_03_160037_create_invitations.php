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
        Schema::create('invitations', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Foreign keys
            $table->unsignedBigInteger('clubhouse_id');
            $table->unsignedBigInteger('invited_by');
            $table->unsignedBigInteger('invitee_user_id')->nullable();

            // Invite details
            $table->string('invitee_email')->nullable();
            $table->string('role', 50);
            $table->string('token', 255)->unique();
            $table->string('status', 20)->default('pending');

            // Timestamps
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            // Define foreign key constraints
            $table->foreign('clubhouse_id')
                  ->references('id')
                  ->on('clubhouses')
                  ->onDelete('cascade');

            $table->foreign('invited_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('invitee_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
