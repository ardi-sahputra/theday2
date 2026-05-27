<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('wedding_type', 30)->nullable()->after('event_type');
            $table->string('city', 120)->nullable()->after('wedding_type');
            $table->string('intended_plan', 20)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['wedding_type', 'city', 'intended_plan']);
        });
    }
};
