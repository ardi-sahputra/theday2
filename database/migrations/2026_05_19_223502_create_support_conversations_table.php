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
        Schema::create('support_conversations', function (Blueprint $t) {
            $t->id();
            $t->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('last_message_at')->nullable()->index();
            $t->timestamp('last_user_message_at')->nullable();
            $t->timestamp('last_admin_message_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->unsignedInteger('unread_by_user_count')->default(0);
            $t->unsignedInteger('unread_by_admin_count')->default(0);
            $t->timestamps();

            $t->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_conversations');
    }
};
