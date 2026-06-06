<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_budget_items', function (Blueprint $table) {
            // Link a budget item to its source vendor. Vendor is the source of truth
            // for cost & payment; a linked item mirrors those values read-only.
            // Nullable: items for non-vendor spend (rings, mahar, honeymoon) stay manual.
            $table->foreignUuid('vendor_id')->nullable()->after('category_id')
                ->constrained('vendors')->nullOnDelete();

            // Enforce 1 vendor = 1 budget item. MySQL allows many NULLs under a
            // unique index, so manual (unlinked) items remain unconstrained.
            $table->unique('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_budget_items', function (Blueprint $table) {
            $table->dropUnique(['vendor_id']);
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};
