<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->foreignUuid('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans')->restrictOnDelete();

            $table->string('recipient_email')->nullable();
            $table->string('delivery_mode');               // 'link' | 'email'
            $table->string('source');                       // 'user' | 'admin'

            $table->integer('duration_days');
            $table->decimal('amount', 12, 2);
            $table->string('message', 280)->nullable();

            $table->string('status');                       // awaiting_payment|pending|claimed|expired
            $table->foreignUuid('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expires_at');

            $table->timestamps();

            $table->index('sender_user_id');
            $table->index('claimed_by_user_id');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
