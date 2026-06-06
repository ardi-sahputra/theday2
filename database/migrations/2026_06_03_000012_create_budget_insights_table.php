<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('wedding_budgets')->cascadeOnDelete()->unique();
            // Hash of the data the insights were generated from. If it still
            // matches the current budget state, we serve these without calling AI.
            $table->string('data_hash', 32);
            $table->json('insights');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_insights');
    }
};
