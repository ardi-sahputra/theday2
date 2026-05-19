<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('couple_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('partner_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('invited_email');
            $table->string('token_hash', 64)->unique();
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            $table->timestamp('invited_at');
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_links');
    }
};
