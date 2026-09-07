<?php

// app/Console/Commands/MigratePlansCommand.php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigratePlansCommand extends Command
{
    protected $signature = 'theday:migrate-plans';

    protected $description = 'Migrate from 3-tier (Free/Silver/Gold) to 2-tier (Free/Premium) pricing';

    public function handle(): int
    {
        $this->info('TheDay — Plan Migration: 3-tier → 2-tier');
        $this->newLine();

        return DB::transaction(function (): int {
            // ── Step 1: Ensure Premium plan exists ────────────────────────
            $premium = $this->ensurePremiumPlan();
            $this->info("✓ Premium plan ready (ID: {$premium->id})");

            // ── Step 2: Migrate active Silver subscriptions ───────────────
            $silverCount = $this->migrateSubscriptions('silver', $premium);
            $this->info("✓ Silver → Premium: {$silverCount} subscription(s) migrated");

            // ── Step 3: Migrate active Gold subscriptions ─────────────────
            $goldCount = $this->migrateSubscriptions('gold', $premium);
            $this->info("✓ Gold → Premium: {$goldCount} subscription(s) migrated");

            // ── Step 4: Deactivate Silver and Gold plans ──────────────────
            $deactivated = Plan::whereIn('slug', ['silver', 'gold'])->update(['is_active' => false]);
            $this->info("✓ Deactivated {$deactivated} legacy plan(s) (Silver/Gold)");

            // ── Summary ───────────────────────────────────────────────────
            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Silver subscriptions migrated', $silverCount],
                    ['Gold subscriptions migrated',   $goldCount],
                    ['Plans deactivated',             $deactivated],
                ]
            );

            $this->info('Migration complete. All Silver/Gold users now have Premium access.');

            return self::SUCCESS;
        });
    }

    private function ensurePremiumPlan(): Plan
    {
        return Plan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name'                => 'Premium',
                'price'               => 49000,
                'original_price'      => 199000,
                'duration_days'       => 0, // lifetime
                'max_invitations'     => 9999,
                'max_gallery_photos'  => 9999,
                'custom_music'        => true,
                'remove_watermark'    => true,
                'custom_domain'       => true,
                'analytics_access'    => true,
                'features'            => [
                    'Sekali bayar, aktif selamanya',
                    '2 undangan aktif (tambah lewat add-on)',
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
            ]
        );
    }

    private function migrateSubscriptions(string $fromSlug, Plan $premium): int
    {
        $sourcePlan = Plan::where('slug', $fromSlug)->first();

        if (! $sourcePlan) {
            $this->warn("  Plan '{$fromSlug}' not found — skipping.");
            return 0;
        }

        // Active subscriptions on this plan that are not already on Premium
        $subs = Subscription::where('plan_id', $sourcePlan->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get();

        $count = 0;

        foreach ($subs as $sub) {
            $sub->update(['plan_id' => $premium->id]);

            Log::info('theday:migrate-plans — subscription migrated', [
                'subscription_id' => $sub->id,
                'user_id'         => $sub->user_id,
                'from_plan'       => $fromSlug,
                'to_plan'         => 'premium',
                'expires_at'      => $sub->expires_at?->toDateTimeString(),
                'migrated_at'     => now()->toDateTimeString(),
            ]);

            $count++;
        }

        return $count;
    }
}
