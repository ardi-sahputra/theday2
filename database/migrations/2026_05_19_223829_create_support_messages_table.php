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
        Schema::create('support_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('support_conversation_id')->constrained()->cascadeOnDelete();
            $t->uuidMorphs('sender');                              // sender_type (class) + sender_id (uuid)
            $t->enum('sender_role', ['user', 'admin'])->index();   // fast filter
            $t->text('body')->nullable();
            $t->string('attachment_path')->nullable();
            $t->string('attachment_mime')->nullable();
            $t->unsignedInteger('attachment_size')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();

            $t->index(['support_conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
