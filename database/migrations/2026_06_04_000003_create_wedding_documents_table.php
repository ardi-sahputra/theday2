<?php

// database/migrations/2026_06_04_000003_create_wedding_documents_table.php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wedding_plan_id')->constrained('wedding_plans')->cascadeOnDelete();
            $table->string('key');                       // matches catalog key
            $table->string('status')->default('belum');  // belum|proses|beres
            $table->string('file_path')->nullable();     // private disk path
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['wedding_plan_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_documents');
    }
};
