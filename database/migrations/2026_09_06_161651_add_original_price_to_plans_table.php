<?php

// database/migrations/2026_09_06_161651_add_original_price_to_plans_table.php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Static "coret harga" anchor for pricing pages, independent from the
     * time-boxed percent discounts in plan_discounts. Null = no anchor shown.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('original_price');
        });
    }
};
