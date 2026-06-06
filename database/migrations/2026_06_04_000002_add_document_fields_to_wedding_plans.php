<?php

// database/migrations/2026_06_04_000002_add_document_fields_to_wedding_plans.php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_plans', function (Blueprint $table) {
            // null until the couple picks a jalur on the Dokumen tab.
            $table->string('document_path')->nullable()->after('checklist_initialized_at');
            // { beda_domisili, under21, under19, widowed, tni_polri, late_register }
            $table->json('document_flags')->nullable()->after('document_path');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_plans', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'document_flags']);
        });
    }
};
