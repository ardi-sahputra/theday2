<?php

// database/seeders/PlanSeeder.php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                => 'Free',
                'slug'                => 'free',
                'price'               => 0,
                'duration_days'       => 0,
                'max_invitations'     => 1,
                'max_gallery_photos'  => 5,
                'custom_music'        => false,
                'remove_watermark'    => false,
                'custom_domain'       => false,
                'analytics_access'    => false,
                'features'            => [
                    'Template dasar (10+)',
                    'Konfirmasi RSVP',
                    'Link undangan',
                    'Peta lokasi',
                ],
                'is_active'           => true,
                'sort_order'          => 1,
            ],
            [
                'name'                => 'Premium',
                'slug'                => 'premium',
                'price'               => 49000,
                'duration_days'       => 365,
                'max_invitations'     => 2,
                'max_gallery_photos'  => 9999,
                'custom_music'        => true,
                'remove_watermark'    => true,
                'custom_domain'       => true,
                'analytics_access'    => true,
                'features'            => [
                    'Undangan tidak terbatas',
                    'Semua template (50+)',
                    'Upload musik sendiri',
                    'Foto galeri tidak terbatas',
                    'Analitik lengkap',
                    'Tanpa watermark',
                    'Perlindungan kata sandi',
                    'Prioritas dukungan',
                ],
                'is_active'           => true,
                'sort_order'          => 2,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
