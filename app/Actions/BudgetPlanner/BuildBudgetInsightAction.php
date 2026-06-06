<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Models\BudgetInsight;
use App\Models\Vendor;
use App\Models\WeddingBudget;
use App\Services\DeepSeekClient;
use App\Support\VendorCategories;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Generate grounded budget insights via DeepSeek. The model only ever sees
 * concrete numbers we compute here, and is instructed to reason solely from
 * them — no inventing prices, dates, or categories (anti-halu).
 *
 * Result is cached against a hash of the context, so a fresh LLM call only
 * happens when the underlying budget/vendor data actually changes.
 */
final class BuildBudgetInsightAction
{
    /** Max fresh LLM generations per user per day (cache hits don't count). */
    private const DAILY_GENERATION_CAP = 30;

    /** Bump when the prompt changes so cached insights regenerate. */
    private const PROMPT_VERSION = 'v2';

    public function __construct(
        private readonly BuildBudgetSummaryAction $summary,
        private readonly DeepSeekClient $deepseek,
    ) {}

    /**
     * Insights are persisted in the budget_insights table, keyed by a hash of the
     * underlying data. While the hash matches the current state we serve the
     * stored row — no API call, no quota spent.
     *
     * @param  bool  $generate  false (page load) = never call AI, just return what's
     *                          stored + a `fresh` flag. true (explicit refresh
     *                          endpoint) = regenerate when stale.
     * @return array{enabled: bool, insights: array<int, array<string, mixed>>, fresh: bool, limited?: bool}
     */
    public function execute(WeddingBudget $budget, bool $generate = false): array
    {
        if (! $this->deepseek->configured()) {
            return ['enabled' => false, 'insights' => [], 'fresh' => true];
        }

        $context = $this->buildContext($budget);

        // Nothing meaningful to advise on yet.
        if ($context['total_budget'] === null && $context['kategori'] === []) {
            return ['enabled' => true, 'insights' => [], 'fresh' => true];
        }

        $hash   = md5(self::PROMPT_VERSION.json_encode($context));
        $stored = BudgetInsight::query()->where('budget_id', $budget->id)->first();

        // Stored insights still match the current data → serve as-is.
        if ($stored !== null && $stored->data_hash === $hash) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => true];
        }

        // Stale or missing. On a plain page load we don't generate — return the
        // last stored insights (if any) and let the client trigger a refresh.
        if (! $generate) {
            return [
                'enabled'  => true,
                'insights' => $stored->insights ?? [],
                'fresh'    => false,
            ];
        }

        // Explicit refresh, but guard the daily quota against data-flip abuse.
        if (! $this->withinDailyQuota($budget->user_id)) {
            return [
                'enabled'  => true,
                'insights' => $stored->insights ?? [],
                'fresh'    => false,
                'limited'  => true,
            ];
        }

        $result   = $this->deepseek->jsonCompletion(
            $this->systemPrompt(),
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        );
        $insights = $this->normalize($result);

        BudgetInsight::query()->updateOrCreate(
            ['budget_id' => $budget->id],
            ['data_hash' => $hash, 'insights' => $insights, 'generated_at' => now()],
        );

        return ['enabled' => true, 'insights' => $insights, 'fresh' => true];
    }

    /**
     * Increment the per-user daily counter; returns false once the cap is hit.
     */
    private function withinDailyQuota(?string $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        $key = 'budget_insight_quota:'.$userId.':'.now()->format('Y-m-d');

        // Seed the counter (expires end of day-ish) then atomically increment.
        Cache::add($key, 0, now()->addDay());
        $count = Cache::increment($key);

        return $count <= self::DAILY_GENERATION_CAP;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(WeddingBudget $budget): array
    {
        $summary = $this->summary->execute($budget);

        $categories = collect($summary['categories'])
            ->map(function (array $c): array {
                $status = 'aman';
                if ($c['planned'] > 0 && $c['actual'] > $c['planned']) {
                    $status = 'melebihi';
                } elseif ($c['planned'] > 0 && $c['actual'] / $c['planned'] >= 0.8) {
                    $status = 'mendekati';
                } elseif ($c['actual'] === 0) {
                    $status = 'belum_terpakai';
                }

                return [
                    'nama'     => $c['name'],
                    'rencana'  => $c['planned'],
                    'terpakai' => $c['actual'],
                    'status'   => $status,
                ];
            })
            ->all();

        $vendors = Vendor::query()
            ->where('user_id', $budget->user_id)
            ->get(['name', 'category', 'total_cost', 'paid_amount']);

        $vendorList = $vendors->map(function (Vendor $v): array {
            $total = (int) ($v->total_cost ?? 0);
            $paid  = (int) ($v->paid_amount ?? 0);
            $status = 'booked';
            if ($total > 0 && $paid >= $total) {
                $status = 'lunas';
            } elseif ($paid > 0) {
                $status = 'dp';
            }

            return [
                'nama'      => $v->name,
                'kategori'  => VendorCategories::label($v->category) ?? $v->category,
                'biaya'     => $total,
                'terbayar'  => $paid,
                'status'    => $status,
            ];
        })->all();

        // Important vendor categories not yet covered by any vendor.
        $presentKeys = $vendors->pluck('category')->unique()->values()->all();
        $gaps = collect(VendorCategories::gap($presentKeys))->pluck('label')->all();

        // Upcoming, not-yet-settled payments with a due date.
        $today = Carbon::today();
        $upcoming = $budget->activeItems()
            ->whereNotNull('due_date')
            ->with('vendor')
            ->get()
            ->filter(fn ($i) => $i->computed_payment_status !== 'paid')
            ->sortBy('due_date')
            ->take(5)
            ->map(fn ($i) => [
                'item'         => $i->title,
                'jatuh_tempo'  => $i->due_date->format('Y-m-d'),
                'hari_lagi'    => (int) round($today->diffInDays($i->due_date, false)),
                'rencana'      => (int) $i->planned_amount,
                'terpakai'     => (int) $i->terpakai,
            ])
            ->values()
            ->all();

        return [
            'mata_uang'        => 'IDR',
            'tanggal_hari_ini' => $today->format('Y-m-d'),
            'total_budget'     => $summary['total_budget'],
            'total_rencana'    => $summary['total_planned'],
            'total_terpakai'   => $summary['total_actual'],
            'sisa_budget'      => $summary['remaining_budget'],
            'persen_terpakai'  => $summary['usage_percentage'],
            'overbudget'       => $summary['is_total_overbudget'],
            'kategori'         => $categories,
            'vendor'           => $vendorList,
            'kategori_penting_belum_ada' => $gaps,
            'pembayaran_akan_datang'     => $upcoming,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Kamu penasihat anggaran pernikahan untuk aplikasi TheDay. Kamu menerima data anggaran
pasangan dalam JSON (mata uang Rupiah). Tugasmu memberi 2-3 insight yang ringkas, spesifik,
dan bisa ditindaklanjuti.

PENGERTIAN DATA (penting, jangan keliru):
- "total_budget" = plafon/anggaran keseluruhan yang ditetapkan pasangan. Bisa null = BELUM diisi.
- "total_rencana" = jumlah "rencana" dari item yang sudah dibuat. Ini BUKAN plafon, dan bisa jauh
  di bawah anggaran sebenarnya kalau baru sebagian kategori diisi.
- "total_terpakai" = yang sudah benar-benar dibayar.

ATURAN KETAT (anti-halusinasi):
- HANYA gunakan angka yang ada di data. Dilarang mengarang harga, tanggal, atau kategori.
- Jika "total_budget" null/kosong: JANGAN perlakukan "total_rencana" sebagai plafon atau batas.
  Jangan bilang "agar tidak melebihi Rp X". Sebaliknya, insight UTAMA: ajak pasangan menetapkan
  total budget pernikahan dulu supaya sisa anggaran bisa dilacak.
- Jika "total_budget" ada: baru boleh bandingkan terpakai/rencana terhadapnya (mis. sisa, % terpakai).
- Jangan menebak tanggal pernikahan. Hitung waktu hanya dari "jatuh_tempo" dan "tanggal_hari_ini".
- Jika data belum cukup untuk suatu insight, jangan paksakan.
- Sebut angka konkret (mis. "Catering lewat 7jt dari rencana") bila relevan.
- Bahasa Indonesia, hangat tapi to-the-point. Maks 1-2 kalimat per insight.

Balas HANYA JSON dengan bentuk:
{
  "insights": [
    {
      "type": "overspend | cashflow | idle | gap | tip",
      "severity": "info | warning | alert",
      "title": "judul singkat (maks 6 kata)",
      "body": "saran 1-2 kalimat dengan angka konkret"
    }
  ]
}
Urut dari paling penting. Maksimal 3 insight.
PROMPT;
    }

    /**
     * @param  array<string, mixed>|null  $result
     * @return array<int, array<string, mixed>>
     */
    private function normalize(?array $result): array
    {
        $items = $result['insights'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $allowedType = ['overspend', 'cashflow', 'idle', 'gap', 'tip'];
        $allowedSev  = ['info', 'warning', 'alert'];

        return collect($items)
            ->filter(fn ($i) => is_array($i) && ! empty($i['title']) && ! empty($i['body']))
            ->take(3)
            ->map(fn (array $i) => [
                'type'     => in_array($i['type'] ?? '', $allowedType, true) ? $i['type'] : 'tip',
                'severity' => in_array($i['severity'] ?? '', $allowedSev, true) ? $i['severity'] : 'info',
                'title'    => mb_substr((string) $i['title'], 0, 60),
                'body'     => mb_substr((string) $i['body'], 0, 240),
            ])
            ->values()
            ->all();
    }
}
