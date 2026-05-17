<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('guest_enabled')->default(true);
            $table->boolean('payment_enabled')->default(true);
            $table->boolean('gift_enabled')->default(true);
            $table->boolean('reminder_enabled')->default(true);
            $table->boolean('onboarding_enabled')->default(true);
            $table->boolean('engagement_enabled')->default(true);
            $table->boolean('system_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
