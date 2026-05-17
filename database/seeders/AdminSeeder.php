<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        $name = env('ADMIN_NAME', 'Super Admin');

        if (! $email || ! $password) {
            $this->command->warn('AdminSeeder skipped: ADMIN_EMAIL or ADMIN_PASSWORD missing in .env');
            return;
        }

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name'      => $name,
                'password'  => $password,
                'role'      => 'super_admin',
                'is_active' => true,
            ],
        );

        $this->command->info("Admin '{$email}' seeded.");
    }
}
