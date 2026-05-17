<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('invitation_sections', 'is_visible_in_template')) {
            return;
        }

        Schema::table('invitation_sections', function (Blueprint $table) {
            $table->boolean('is_visible_in_template')->default(true)->after('is_required');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('invitation_sections', 'is_visible_in_template')) {
            Schema::table('invitation_sections', function (Blueprint $table) {
                $table->dropColumn('is_visible_in_template');
            });
        }
    }
};
