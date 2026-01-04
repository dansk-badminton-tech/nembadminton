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
        Schema::create('team_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('team_id', 24);
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->string('action'); // e.g., 'notification_sent', 'test_email_sent'
            $table->string('recipient_type'); // 'platform_users' or 'manual_emails'
            $table->integer('recipient_count')->default(0);
            $table->text('recipients_summary')->nullable(); // Summary of who received it
            $table->text('message')->nullable(); // The message that was sent
            $table->json('metadata')->nullable(); // Additional data (email addresses, user IDs, etc.)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who performed the action
            $table->timestamps();

            $table->index('team_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_fight_activity_logs');
    }
};
