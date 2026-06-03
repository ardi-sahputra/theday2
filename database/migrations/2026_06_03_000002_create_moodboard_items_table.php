<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moodboard_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('moodboard_id')->constrained('moodboards')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('image_url');
            $table->string('caption')->nullable();
            $table->string('tag')->nullable();
            $table->json('colors')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['moodboard_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodboard_items');
    }
};
