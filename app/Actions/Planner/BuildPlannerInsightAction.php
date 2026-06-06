<?php

declare(strict_types=1);

namespace App\Actions\Planner;

use App\Models\PlannerInsight;
use App\Models\Vendor;
use App\Models\WeddingPlan;
use App\Services\DeepSeekClient;
use App\Support\VendorCategories;
use Illuminate\Support\Facades\Cache;

/**
 * AI planner cards. Mirrors BuildBudgetInsightAction: grounded context → hash →
 * planner_insights row. Serves stored cards while the hash matches; only calls
 * DeepSeek on generate:true within the daily quota.
 */
final class BuildPlannerInsightAction
{
    private const DAILY_GENERATION_CAP = 30;
    private const PROMPT_VERSION = 'v1';

    public function __construct(
        private readonly BuildPlannerFactsAction $facts,
        private readonly DeepSeekClient $deepseek,
    ) {}

    /**
     * @return array{enabled:bool, insights:array<int,array<string,mixed>>, fresh:bool, limited?:bool}
     */
    public function execute(WeddingPlan $plan, bool $generate = false): array
    {
        if (! $this->deepseek->configured()) {
            return ['enabled' => false, 'insights' => [], 'fresh' => true];
        }

        $context = $this->buildContext($plan);

        if ($context['hari_menuju_hari_h'] === null && $context['checklist']['total'] === 0 && $context['budget']['has_budget'] === false) {
            return ['enabled' => true, 'insights' => [], 'fresh' => true];
        }

        $hash   = md5(self::PROMPT_VERSION.json_encode($context));
        $stored = PlannerInsight::query()->where('wedding_plan_id', $plan->id)->first();

        if ($stored !== null && $stored->data_hash === $hash) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => true];
        }

        if (! $generate) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => false];
        }

        if (! $this->withinDailyQuota($plan->user_id)) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => false, 'limited' => true];
        }

        $result   = $this->deepseek->jsonCompletion(
            $this->systemPrompt(),
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        );
        $insights = $this->normalize($result);

        PlannerInsight::query()->updateOrCreate(
            ['wedding_plan_id' => $plan->id],
            ['data_hash' => $hash, 'insights' => $insights, 'generated_at' => now()],
        );

        return ['enabled' => true, 'insights' => $insights, 'fresh' => true];
    }

    private function withinDailyQuota(?string $userId): bool
    {
        if ($userId === null) {
            return false;
        }
        $key = 'planner_insight_quota:'.$userId.':'.now()->format('Y-m-d');
        Cache::add($key, 0, now()->addDay());

        return Cache::increment($key) <= self::DAILY_GENERATION_CAP;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(WeddingPlan $plan): array
    {
        $facts = $this->facts->execute($plan);

        $vendors = Vendor::query()
            ->where('user_id', $plan->user_id)
            ->get(['name', 'category', 'total_cost', 'paid_amount']);

        $vendorList = $vendors->map(function (Vendor $v): array {
            $total = (int) ($v->total_cost ?? 0);
            $paid  = (int) ($v->paid_amount ?? 0);
            $status = $total > 0 && $paid >= $total ? 'lunas' : ($paid > 0 ? 'dp' : 'booked');

            return [
                'nama'     => $v->name,
                'kategori' => VendorCategories::label($v->category) ?? $v->category,
                'status'   => $status,
            ];
        })->all();

        $gaps = collect(VendorCategories::gap($vendors->pluck('category')->unique()->values()->all()))
            ->pluck('label')->all();

        return [
            'tanggal_hari_ini'    => now()->format('Y-m-d'),
            'hari_menuju_hari_h'  => $facts['days_to_go'],
            'checklist'           => $facts['checklist'],
            'budget'              => $facts['budget'],
            'pembayaran_akan_datang' => $facts['payments_due'],
            'vendor'              => $vendorList,
            'kategori_penting_belum_ada' => $gaps,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Kamu penasihat pernikahan untuk aplikasi TheDay. Kamu menerima data persiapan pasangan
dalam JSON (Rupiah). Tujuanmu membuat pasangan merasa TENANG dan TERBIMBING — beri 2-3
arahan singkat, paling penting dulu.

PRIORITAS:
1. Risiko (paling penting): task overdue, pembayaran jatuh tempo, forecast budget lewat plafon.
2. Fokus minggu ini: berdasarkan checklist & hari menuju hari H.
3. Langkah berikutnya (prioritas terendah, frame sebagai SARAN, bukan fakta): kategori vendor
   penting yang belum ada.

ATURAN KETAT (anti-halusinasi):
- HANYA gunakan angka di data. Dilarang mengarang harga, tanggal, atau kategori.
- "hari_menuju_hari_h" sudah diberikan. Jangan menebak tanggal pernikahan. Jika null, jangan
  mengarang timeline — sarankan menetapkan tanggal.
- "target" WAJIB salah satu dari: "budget", "vendor", "checklist", atau null. Jangan membuat URL.
- Maks 3 kartu, 1-2 kalimat per kartu. Bahasa Indonesia, hangat, to-the-point.
- Gabungkan sinyal lintas-domain bila relevan (mis. "pembayaran jatuh tempo DAN banyak task").

Balas HANYA JSON:
{
  "insights": [
    { "severity": "alert|warning|info", "title": "judul singkat", "body": "saran 1-2 kalimat", "target": "budget|vendor|checklist|null" }
  ]
}
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

        $allowedSev    = ['info', 'warning', 'alert'];
        $allowedTarget = ['budget', 'vendor', 'checklist'];

        return collect($items)
            ->filter(fn ($i) => is_array($i) && ! empty($i['title']) && ! empty($i['body']))
            ->take(3)
            ->map(fn (array $i) => [
                'severity' => in_array($i['severity'] ?? '', $allowedSev, true) ? $i['severity'] : 'info',
                'title'    => mb_substr((string) $i['title'], 0, 60),
                'body'     => mb_substr((string) $i['body'], 0, 240),
                'target'   => in_array($i['target'] ?? '', $allowedTarget, true) ? $i['target'] : null,
            ])
            ->values()
            ->all();
    }
}
