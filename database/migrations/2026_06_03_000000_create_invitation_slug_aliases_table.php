<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Old invitation slugs kept after a rename, so links already shared keep
     * working (public route 301-redirects an alias to the current slug).
     */
    public function up(): void
    {
        Schema::create('invitation_slug_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_slug_aliases');
    }
};
