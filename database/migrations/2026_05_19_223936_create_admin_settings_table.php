<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->json('value');
            $t->timestamps();
        });

        DB::table('admin_settings')->insert([
            'key'        => 'support_work_hours',
            'value'      => json_encode([
                'timezone' => 'Asia/Jakarta',
                'days'     => [1, 2, 3, 4, 5, 6],
                'start'    => '09:00',
                'end'      => '18:00',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
