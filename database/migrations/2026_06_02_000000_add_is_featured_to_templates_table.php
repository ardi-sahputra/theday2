<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Curated "hero" flag — featured templates surface at the top of the
     * gallery regardless of tier, so the best work (free + premium) leads.
     */
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
