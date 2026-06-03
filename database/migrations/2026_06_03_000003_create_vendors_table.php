<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('category', 40);
            $table->string('pic_name', 80)->nullable();
            $table->string('phone', 30)->nullable();
            $table->unsignedBigInteger('total_cost')->nullable();
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('next_action', 120)->nullable();
            $table->date('booked_at')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->string('contract_path')->nullable();
            $table->string('contract_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'booked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
