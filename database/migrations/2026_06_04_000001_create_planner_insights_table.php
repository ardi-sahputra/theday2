<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planner_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('wedding_plan_id')->constrained('wedding_plans')->cascadeOnDelete()->unique();
            $table->string('data_hash', 32);
            $table->json('insights');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_insights');
    }
};
