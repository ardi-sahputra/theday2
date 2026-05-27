<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_budget_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('wedding_budgets')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['budget_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_budget_notes');
    }
};
