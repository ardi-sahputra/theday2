<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invitation_sections')) {
            return;
        }

        Schema::create('invitation_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->foreignUuid('template_section_id')->nullable()->constrained('template_sections')->nullOnDelete();
            $table->string('section_key');
            $table->string('section_type');
            $table->string('label')->nullable();
            $table->foreignUuid('variant_id')->nullable()->constrained('section_variants')->nullOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('completion_status', ['empty', 'incomplete', 'complete', 'warning', 'error'])
                  ->default('empty');
            $table->json('validation_errors_json')->nullable();
            $table->json('data_json')->nullable();
            $table->json('style_json')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();

            $table->unique(['invitation_id', 'section_key']);
            $table->index('invitation_id');
            $table->index('section_type');
            $table->index('is_enabled');
            $table->index('completion_status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_sections');
    }
};
