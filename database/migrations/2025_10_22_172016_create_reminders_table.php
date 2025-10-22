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
    Schema::create('reminders', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->dateTime('remind_at');
        $table->string('fcm_token')->nullable(); // user's device FCM token
        $table->boolean('notified')->default(false);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
