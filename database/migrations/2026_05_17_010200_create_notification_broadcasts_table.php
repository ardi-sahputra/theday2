<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_broadcasts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('admin_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->string('action_url', 255)->nullable();
            $table->string('category', 20)->default('system');
            $table->enum('target_type', ['all', 'users']);
            $table->json('target_user_ids')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();

            $table->index(['sent_at', 'cancelled_at', 'scheduled_at'], 'notif_bcast_dispatch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_broadcasts');
    }
};
