<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 20);
            $table->string('type', 50);
            $table->string('group_key', 100)->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->string('action_url', 255)->nullable();
            $table->json('payload')->nullable();
            $table->string('template_key', 100)->nullable();
            $table->string('locale', 10)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'updated_at'], 'user_notif_user_read_updated_idx');
            $table->index(['user_id', 'group_key', 'read_at'], 'user_notif_group_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
