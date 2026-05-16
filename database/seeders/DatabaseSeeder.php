<?php

// database/seeders/DatabaseSeeder.php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Core data ─────────────────────────────────────────────
        $this->call([
            PlanSeeder::class,
            TemplateCategorySeeder::class,
            TemplateSeeder::class,
            ChecklistTemplateSeeder::class,
            BlogSeeder::class,
            AdminSeeder::class,
        ]);

        // // ── Admin user ────────────────────────────────────────────
        // User::factory()->admin()->create([
        //     'name'  => 'Admin TheDay',
        //     'email' => 'admin@theday.id',
        // ]);

        // // ── Demo user ─────────────────────────────────────────────
        // User::factory()->create([
        //     'name'  => 'Demo User',
        //     'email' => 'demo@theday.id',
        // ]);
    }
}
