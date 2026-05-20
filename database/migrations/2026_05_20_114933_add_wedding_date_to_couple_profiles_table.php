<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('couple_profiles', function (Blueprint $t) {
            $t->date('wedding_date')->nullable()->after('bride_parent_names');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('couple_profiles', function (Blueprint $t) {
            $t->dropColumn('wedding_date');
        });
    }
};
