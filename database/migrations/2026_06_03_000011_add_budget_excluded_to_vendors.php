<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Set when the couple removes this vendor's line from the Budget
            // Planner. Tells the sync action NOT to recreate/resurrect it on the
            // next vendor edit. The vendor record itself stays intact.
            $table->boolean('budget_excluded')->default(false)->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('budget_excluded');
        });
    }
};
