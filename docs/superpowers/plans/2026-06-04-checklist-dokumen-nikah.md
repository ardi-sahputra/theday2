# Checklist Dokumen Nikah Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "Dokumen" tab inside the Wedding Planner that lets a couple track, store files for, and read guidance on Indonesian marriage paperwork.

**Architecture:** Static catalog (`config/wedding_documents.php`) is the single source of document labels + guidance + applicability (jalur/conditions). Per-couple progress lives in a new `wedding_documents` table. A `DocumentService` filters the catalog by the couple's chosen jalur (`kua`/`sipil`) + condition flags stored on `WeddingPlan`, and merges in saved status/files. The Vue Planner page gains a `?tab=dokumen` segmented switch rendering a `DocumentsTab`. Files are stored on the private `local` disk and served only via an owner-authorized signed route.

**Tech Stack:** Laravel 11 (Inertia + Eloquent, UUID PKs), Vue 3 `<script setup>`, vue-i18n (JSON lang files), PHPUnit feature tests, Tailwind.

**Spec:** `docs/superpowers/specs/2026-06-04-checklist-dokumen-nikah-design.md`

---

## File Structure

**Backend (create):**
- `app/Enums/WeddingDocumentStatus.php` — status enum (belum/proses/beres)
- `app/Enums/WeddingDocumentPath.php` — jalur enum (kua/sipil)
- `config/wedding_documents.php` — verified catalog (labels + guidance + jalur + conditions)
- `database/migrations/2026_06_04_000001_add_document_fields_to_wedding_plans.php`
- `database/migrations/2026_06_04_000002_create_wedding_documents_table.php`
- `app/Models/WeddingDocument.php`
- `app/Services/WeddingDocumentService.php`
- `app/Http/Controllers/Dashboard/WeddingDocumentController.php`

**Backend (modify):**
- `app/Models/WeddingPlan.php` — fillable + casts + `weddingDocuments()` relation
- `routes/web.php` — document routes inside the `dashboard.` group

**Frontend (create):**
- `resources/js/Components/dashboard/documents/DocumentsTab.vue`
- `resources/js/Components/dashboard/documents/DocumentContextPicker.vue`
- `resources/js/Components/dashboard/documents/DocumentCard.vue`

**Frontend (modify):**
- `resources/js/Pages/Dashboard/Checklist/Index.vue` — `Tugas | Dokumen` tab via `?tab`
- `resources/js/Components/dashboard/widgets/ChecklistCard.vue` — add "Dokumen Nikah" entry (or a sibling card)
- `lang/id.json`, `lang/en.json` — `dashboard.documents.*` keys

**Tests (create):**
- `tests/Unit/Services/WeddingDocumentCatalogTest.php`
- `tests/Feature/Dashboard/WeddingDocumentContextTest.php`
- `tests/Feature/Dashboard/WeddingDocumentStatusTest.php`
- `tests/Feature/Dashboard/WeddingDocumentFileTest.php`

---

## Task 1: Status & jalur enums

**Files:**
- Create: `app/Enums/WeddingDocumentStatus.php`
- Create: `app/Enums/WeddingDocumentPath.php`

- [ ] **Step 1: Create the status enum**

```php
<?php

// app/Enums/WeddingDocumentStatus.php

declare(strict_types=1);

namespace App\Enums;

enum WeddingDocumentStatus: string
{
    case Belum  = 'belum';
    case Proses = 'proses';
    case Beres  = 'beres';
}
```

- [ ] **Step 2: Create the jalur enum**

```php
<?php

// app/Enums/WeddingDocumentPath.php

declare(strict_types=1);

namespace App\Enums;

enum WeddingDocumentPath: string
{
    case Kua   = 'kua';
    case Sipil = 'sipil';
}
```

- [ ] **Step 3: Commit**

```bash
rtk git add app/Enums/WeddingDocumentStatus.php app/Enums/WeddingDocumentPath.php
rtk git commit -m "feat(documents): add status and jalur enums"
```

---

## Task 2: Document catalog config

The catalog is the verified source of truth (spec §5). Each entry:
`key`, `label`, `paths` (which jalur it applies to), `condition` (null = always, else a flag key), `required` (bool), and `guidance` (`where`, `requirements`, `lead_days` relative to event date, `order`).

**Files:**
- Create: `config/wedding_documents.php`
- Test: `tests/Unit/Services/WeddingDocumentCatalogTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

// tests/Unit/Services/WeddingDocumentCatalogTest.php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;

class WeddingDocumentCatalogTest extends TestCase
{
    public function test_catalog_has_universal_and_path_specific_entries(): void
    {
        $catalog = collect(config('wedding_documents.catalog'));

        // Universal entries apply to both jalur.
        $ktp = $catalog->firstWhere('key', 'ktp');
        $this->assertNotNull($ktp);
        $this->assertEqualsCanonicalizing(['kua', 'sipil'], $ktp['paths']);

        // KUA-only N1 form.
        $n1 = $catalog->firstWhere('key', 'n1');
        $this->assertSame(['kua'], $n1['paths']);

        // Sipil-only pemberkatan.
        $pemberkatan = $catalog->firstWhere('key', 'pemberkatan');
        $this->assertSame(['sipil'], $pemberkatan['paths']);

        // Conditional N5 gated by under21 flag.
        $n5 = $catalog->firstWhere('key', 'n5');
        $this->assertSame('under21', $n5['condition']);

        // Numpang nikah gated by beda_domisili flag, kua only.
        $numpang = $catalog->firstWhere('key', 'numpang_nikah');
        $this->assertSame('beda_domisili', $numpang['condition']);
        $this->assertSame(['kua'], $numpang['paths']);
    }

    public function test_every_entry_has_required_shape(): void
    {
        foreach (config('wedding_documents.catalog') as $entry) {
            $this->assertArrayHasKey('key', $entry);
            $this->assertArrayHasKey('label', $entry);
            $this->assertArrayHasKey('paths', $entry);
            $this->assertArrayHasKey('condition', $entry);   // null or flag string
            $this->assertArrayHasKey('guidance', $entry);
            $this->assertArrayHasKey('where', $entry['guidance']);
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=WeddingDocumentCatalogTest`
Expected: FAIL — `config('wedding_documents.catalog')` is null.

- [ ] **Step 3: Create the catalog config**

```php
<?php

// config/wedding_documents.php
//
// Verified against 2025 sources (see spec §12). When KUA/Disdukcapil rules
// change, edit THIS file — it is the single source of truth for the Dokumen tab.

declare(strict_types=1);

return [
    // Condition flags a couple can toggle. null condition = always shown.
    'flags' => ['beda_domisili', 'under21', 'under19', 'widowed', 'tni_polri', 'late_register'],

    'catalog' => [
        // ── Universal (both jalur) ──────────────────────────────────────
        [
            'key' => 'ktp', 'label' => 'KTP Elektronik', 'paths' => ['kua', 'sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Sudah dimiliki; siapkan fotokopi tiap pihak.',
                'requirements' => 'KTP-el asli + fotokopi kedua calon.',
                'order' => 1, 'lead_days' => 45,
            ],
        ],
        [
            'key' => 'kk', 'label' => 'Kartu Keluarga (KK)', 'paths' => ['kua', 'sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Sudah dimiliki; siapkan fotokopi.',
                'requirements' => 'KK asli + fotokopi kedua calon.',
                'order' => 2, 'lead_days' => 45,
            ],
        ],
        [
            'key' => 'akta_lahir', 'label' => 'Akta Kelahiran', 'paths' => ['kua', 'sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Disdukcapil jika belum punya.',
                'requirements' => 'Akta asli + fotokopi.',
                'order' => 3, 'lead_days' => 45,
            ],
        ],
        [
            'key' => 'pas_foto', 'label' => 'Pas Foto 2×3 & 4×6 (latar biru)', 'paths' => ['kua', 'sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Studio foto.',
                'requirements' => 'Latar biru. Posisi berdampingan: pria kanan, wanita kiri.',
                'order' => 4, 'lead_days' => 30,
            ],
        ],
        [
            'key' => 'pengantar_rt_rw', 'label' => 'Surat Pengantar RT/RW', 'paths' => ['kua', 'sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Ketua RT lalu RW domisili.',
                'requirements' => 'Bawa KTP + KK. Jadi dasar surat kelurahan.',
                'order' => 5, 'lead_days' => 30,
            ],
        ],

        // ── Jalur Islam / KUA ───────────────────────────────────────────
        [
            'key' => 'n1', 'label' => 'N1 — Surat Pengantar Nikah', 'paths' => ['kua'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Kelurahan/Desa (setelah pengantar RT/RW).',
                'requirements' => 'Surat pengantar RT/RW, KTP, KK.',
                'order' => 6, 'lead_days' => 25,
            ],
        ],
        [
            'key' => 'n2', 'label' => 'N2 — Surat Keterangan Asal-Usul', 'paths' => ['kua'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Kelurahan/Desa.',
                'requirements' => 'Diurus bersama N1.',
                'order' => 7, 'lead_days' => 25,
            ],
        ],
        [
            'key' => 'n3', 'label' => 'N3 — Surat Persetujuan Mempelai', 'paths' => ['kua'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Diisi kedua calon, diserahkan ke KUA.',
                'requirements' => 'Tanda tangan kedua calon.',
                'order' => 8, 'lead_days' => 25,
            ],
        ],
        [
            'key' => 'n4', 'label' => 'N4 — Surat Keterangan Tentang Orang Tua', 'paths' => ['kua'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Kelurahan/Desa.',
                'requirements' => 'Data orang tua kedua calon.',
                'order' => 9, 'lead_days' => 25,
            ],
        ],
        [
            'key' => 'bimwin', 'label' => 'Sertifikat Bimbingan Perkawinan (Bimwin/Suscatin)', 'paths' => ['kua'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'KUA / BP4 — ikuti jadwal bimbingan.',
                'requirements' => 'Hadir bimbingan. Daftar jauh hari karena kuota terbatas.',
                'order' => 10, 'lead_days' => 20,
            ],
        ],
        [
            'key' => 'layak_kawin', 'label' => 'Sertifikat Layak Kawin', 'paths' => ['kua'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Puskesmas domisili.',
                'requirements' => 'Skrining kesehatan + imunisasi TT (umumnya untuk calon istri).',
                'order' => 11, 'lead_days' => 20,
            ],
        ],
        [
            'key' => 'n5', 'label' => 'N5 — Surat Izin Orang Tua', 'paths' => ['kua'],
            'condition' => 'under21', 'required' => true,
            'guidance' => [
                'where' => 'Kelurahan/Desa.',
                'requirements' => 'Wajib jika calon berusia di bawah 21 tahun.',
                'order' => 12, 'lead_days' => 25,
            ],
        ],
        [
            'key' => 'dispensasi_pengadilan', 'label' => 'Dispensasi Pengadilan Agama', 'paths' => ['kua'],
            'condition' => 'under19', 'required' => true,
            'guidance' => [
                'where' => 'Pengadilan Agama.',
                'requirements' => 'Wajib jika calon berusia di bawah 19 tahun.',
                'order' => 13, 'lead_days' => 40,
            ],
        ],
        [
            'key' => 'akta_cerai_kematian', 'label' => 'Akta Cerai / Akta Kematian Pasangan', 'paths' => ['kua', 'sipil'],
            'condition' => 'widowed', 'required' => true,
            'guidance' => [
                'where' => 'Pengadilan Agama (cerai) / Disdukcapil (kematian).',
                'requirements' => 'Wajib bagi duda/janda sebagai bukti status.',
                'order' => 14, 'lead_days' => 30,
            ],
        ],
        [
            'key' => 'izin_atasan', 'label' => 'Surat Izin Atasan (TNI/Polri/PNS)', 'paths' => ['kua', 'sipil'],
            'condition' => 'tni_polri', 'required' => true,
            'guidance' => [
                'where' => 'Komandan/atasan satuan.',
                'requirements' => 'Wajib bagi anggota TNI/Polri (dan sebagian PNS).',
                'order' => 15, 'lead_days' => 30,
            ],
        ],
        [
            'key' => 'dispensasi_camat', 'label' => 'Dispensasi Camat', 'paths' => ['kua'],
            'condition' => 'late_register', 'required' => true,
            'guidance' => [
                'where' => 'Kantor Kecamatan.',
                'requirements' => 'Wajib jika mendaftar kurang dari 10 hari kerja sebelum akad.',
                'order' => 16, 'lead_days' => 10,
            ],
        ],
        [
            'key' => 'numpang_nikah', 'label' => 'Surat Rekomendasi / Numpang Nikah', 'paths' => ['kua'],
            'condition' => 'beda_domisili', 'required' => true,
            'guidance' => [
                'where' => 'KUA asal → diserahkan ke KUA lokasi akad.',
                'requirements' => 'Daftar di KUA lokasi paling lambat 10 hari sebelum akad.',
                'order' => 17, 'lead_days' => 21,
            ],
        ],

        // ── Jalur Sipil / Disdukcapil (non-muslim) ──────────────────────
        [
            'key' => 'pemberkatan', 'label' => 'Surat Pemberkatan Agama (dilegalisir)', 'paths' => ['sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Gereja/lembaga keagamaan.',
                'requirements' => 'Asli + legalisir. Pencatatan maks 60 hari setelah pemberkatan.',
                'order' => 6, 'lead_days' => 14,
            ],
        ],
        [
            'key' => 'ket_belum_menikah', 'label' => 'Surat Keterangan Belum Menikah', 'paths' => ['sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Kelurahan domisili.',
                'requirements' => 'Bawa KTP + KK.',
                'order' => 7, 'lead_days' => 25,
            ],
        ],
        [
            'key' => 'ktp_saksi', 'label' => 'KTP 2 Saksi (usia > 21)', 'paths' => ['sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Dari kedua saksi.',
                'requirements' => 'Fotokopi KTP 2 saksi, masing-masing berusia di atas 21 tahun.',
                'order' => 8, 'lead_days' => 14,
            ],
        ],
        [
            'key' => 'ktp_ortu', 'label' => 'KTP Orang Tua / Wali', 'paths' => ['sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Dari orang tua/wali.',
                'requirements' => 'Fotokopi KTP orang tua/wali kedua calon.',
                'order' => 9, 'lead_days' => 14,
            ],
        ],
        [
            'key' => 'pengantar_lurah', 'label' => 'Surat Pengantar Lurah (asli)', 'paths' => ['sipil'],
            'condition' => null, 'required' => true,
            'guidance' => [
                'where' => 'Kelurahan domisili.',
                'requirements' => 'Asli. Dari pengantar RT/RW.',
                'order' => 10, 'lead_days' => 20,
            ],
        ],
        [
            'key' => 'surat_baptis', 'label' => 'Surat Baptis / Keterangan Agama', 'paths' => ['sipil'],
            'condition' => null, 'required' => false,
            'guidance' => [
                'where' => 'Gereja/lembaga keagamaan.',
                'requirements' => 'Lampirkan jika diminta Disdukcapil setempat.',
                'order' => 11, 'lead_days' => 14,
            ],
        ],
    ],
];
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=WeddingDocumentCatalogTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add config/wedding_documents.php tests/Unit/Services/WeddingDocumentCatalogTest.php
rtk git commit -m "feat(documents): verified marriage-document catalog config"
```

---

## Task 3: Migrations — plan context fields + documents table

**Files:**
- Create: `database/migrations/2026_06_04_000001_add_document_fields_to_wedding_plans.php`
- Create: `database/migrations/2026_06_04_000002_create_wedding_documents_table.php`

- [ ] **Step 1: Create the wedding_plans alteration migration**

```php
<?php

// database/migrations/2026_06_04_000001_add_document_fields_to_wedding_plans.php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_plans', function (Blueprint $table) {
            // null until the couple picks a jalur on the Dokumen tab.
            $table->string('document_path')->nullable()->after('checklist_initialized_at');
            // { beda_domisili, under21, under19, widowed, tni_polri, late_register }
            $table->json('document_flags')->nullable()->after('document_path');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_plans', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'document_flags']);
        });
    }
};
```

- [ ] **Step 2: Create the wedding_documents table migration**

```php
<?php

// database/migrations/2026_06_04_000002_create_wedding_documents_table.php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wedding_plan_id')->constrained('wedding_plans')->cascadeOnDelete();
            $table->string('key');                       // matches catalog key
            $table->string('status')->default('belum');  // belum|proses|beres
            $table->string('file_path')->nullable();     // private disk path
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['wedding_plan_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_documents');
    }
};
```

- [ ] **Step 3: Run the migration**

Run: `php artisan migrate`
Expected: both migrations run green; no errors.

- [ ] **Step 4: Commit**

```bash
rtk git add database/migrations/2026_06_04_000001_add_document_fields_to_wedding_plans.php database/migrations/2026_06_04_000002_create_wedding_documents_table.php
rtk git commit -m "feat(documents): migrations for plan context + documents table"
```

---

## Task 4: WeddingDocument model + WeddingPlan wiring

**Files:**
- Create: `app/Models/WeddingDocument.php`
- Modify: `app/Models/WeddingPlan.php`

- [ ] **Step 1: Create the model**

```php
<?php

// app/Models/WeddingDocument.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WeddingDocumentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'wedding_plan_id',
        'key',
        'status',
        'file_path',
        'note',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => WeddingDocumentStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function weddingPlan(): BelongsTo
    {
        return $this->belongsTo(WeddingPlan::class);
    }
}
```

- [ ] **Step 2: Add fillable + casts + relation to WeddingPlan**

In `app/Models/WeddingPlan.php`, extend `$fillable` (currently lines 19-24):

```php
    protected $fillable = [
        'user_id',
        'primary_invitation_id',
        'event_date',
        'checklist_initialized_at',
        'document_path',
        'document_flags',
    ];
```

Extend `casts()` (currently lines 26-32):

```php
    protected function casts(): array
    {
        return [
            'event_date'               => 'date',
            'checklist_initialized_at' => 'datetime',
            'document_flags'           => 'array',
        ];
    }
```

Add the relation next to `checklistTasks()` (after line 49):

```php
    public function weddingDocuments(): HasMany
    {
        return $this->hasMany(WeddingDocument::class);
    }
```

- [ ] **Step 3: Verify it loads**

Run: `php artisan tinker --execute="echo App\Models\WeddingDocument::class;"`
Expected: prints `App\Models\WeddingDocument` with no class-load error.

- [ ] **Step 4: Commit**

```bash
rtk git add app/Models/WeddingDocument.php app/Models/WeddingPlan.php
rtk git commit -m "feat(documents): WeddingDocument model + plan relation"
```

---

## Task 5: WeddingDocumentService — filter catalog + merge progress

**Files:**
- Create: `app/Services/WeddingDocumentService.php`
- Test: `tests/Unit/Services/WeddingDocumentCatalogTest.php` (extend)

The service resolves which catalog entries apply (by `document_path` + active flags) and merges each with the couple's saved row (status/file/note), producing a view-model array.

- [ ] **Step 1: Write the failing test (append to catalog test file)**

Add to `tests/Unit/Services/WeddingDocumentCatalogTest.php`:

```php
    public function test_service_filters_by_path_and_flags(): void
    {
        $service = app(\App\Services\WeddingDocumentService::class);

        // KUA path, no flags → includes n1, excludes pemberkatan + conditional n5.
        $kua = collect($service->resolveCatalog('kua', []))->pluck('key');
        $this->assertContains('ktp', $kua);
        $this->assertContains('n1', $kua);
        $this->assertNotContains('pemberkatan', $kua);
        $this->assertNotContains('n5', $kua);

        // KUA path + under21 flag → n5 now included.
        $kuaU21 = collect($service->resolveCatalog('kua', ['under21' => true]))->pluck('key');
        $this->assertContains('n5', $kuaU21);

        // KUA + beda_domisili → numpang_nikah included.
        $kuaBeda = collect($service->resolveCatalog('kua', ['beda_domisili' => true]))->pluck('key');
        $this->assertContains('numpang_nikah', $kuaBeda);

        // Sipil path → pemberkatan in, n1 out.
        $sipil = collect($service->resolveCatalog('sipil', []))->pluck('key');
        $this->assertContains('pemberkatan', $sipil);
        $this->assertNotContains('n1', $sipil);
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_service_filters_by_path_and_flags`
Expected: FAIL — service class does not exist.

- [ ] **Step 3: Create the service**

```php
<?php

// app/Services/WeddingDocumentService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\WeddingDocument;
use App\Models\WeddingPlan;

class WeddingDocumentService
{
    /**
     * Catalog entries that apply to the given jalur + active condition flags.
     * Returns plain arrays (catalog shape), sorted by guidance.order.
     *
     * @param  array<string,bool>  $flags
     * @return array<int,array<string,mixed>>
     */
    public function resolveCatalog(?string $path, array $flags): array
    {
        if ($path === null) {
            return [];
        }

        $active = collect($flags)->filter()->keys()->all();

        return collect(config('wedding_documents.catalog'))
            ->filter(fn ($e) => in_array($path, $e['paths'], true))
            ->filter(fn ($e) => $e['condition'] === null || in_array($e['condition'], $active, true))
            ->sortBy(fn ($e) => $e['guidance']['order'])
            ->values()
            ->all();
    }

    /**
     * View-model for the Dokumen tab: each applicable catalog entry merged
     * with the couple's saved row (status/file/note).
     *
     * @return array<int,array<string,mixed>>
     */
    public function buildView(WeddingPlan $plan): array
    {
        $rows = $plan->weddingDocuments()->get()->keyBy('key');

        return collect($this->resolveCatalog($plan->document_path, $plan->document_flags ?? []))
            ->map(function ($entry) use ($rows) {
                $row = $rows->get($entry['key']);

                return [
                    'key'        => $entry['key'],
                    'label'      => $entry['label'],
                    'required'   => $entry['required'],
                    'guidance'   => $entry['guidance'],
                    'status'     => $row?->status?->value ?? 'belum',
                    'has_file'   => $row?->file_path !== null,
                    'note'       => $row?->note,
                ];
            })
            ->all();
    }

    /**
     * Get-or-create the per-couple row for a catalog key.
     */
    public function rowFor(WeddingPlan $plan, string $key): WeddingDocument
    {
        return $plan->weddingDocuments()->firstOrCreate(['key' => $key]);
    }

    /**
     * True if the key is a valid catalog key (guards arbitrary input).
     */
    public function isValidKey(string $key): bool
    {
        return collect(config('wedding_documents.catalog'))->contains('key', $key);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=WeddingDocumentCatalogTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/WeddingDocumentService.php tests/Unit/Services/WeddingDocumentCatalogTest.php
rtk git commit -m "feat(documents): catalog filter + view-model service"
```

---

## Task 6: Controller + routes — context & status

**Files:**
- Create: `app/Http/Controllers/Dashboard/WeddingDocumentController.php`
- Modify: `routes/web.php` (inside the `dashboard.` group, after the Checklist block at line 290)
- Test: `tests/Feature/Dashboard/WeddingDocumentContextTest.php`
- Test: `tests/Feature/Dashboard/WeddingDocumentStatusTest.php`

- [ ] **Step 1: Write the failing context test**

```php
<?php

// tests/Feature/Dashboard/WeddingDocumentContextTest.php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingDocumentContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_can_set_path_and_flags(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/context', [
                'path'  => 'kua',
                'flags' => ['beda_domisili' => true, 'under21' => false],
            ])
            ->assertSuccessful();

        $plan = $user->weddingPlan()->first();
        $this->assertSame('kua', $plan->document_path);
        $this->assertTrue($plan->document_flags['beda_domisili']);
    }

    public function test_data_endpoint_returns_filtered_catalog(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua', 'document_flags' => ['beda_domisili' => true]]);

        $res = $this->actingAs($user)->getJson('/dashboard/documents/data')->assertSuccessful();

        $keys = collect($res->json('documents'))->pluck('key');
        $this->assertContains('n1', $keys);
        $this->assertContains('numpang_nikah', $keys);
        $this->assertNotContains('pemberkatan', $keys);
    }

    public function test_invalid_path_is_rejected(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/context', ['path' => 'bogus'])
            ->assertStatus(422);
    }
}
```

- [ ] **Step 2: Write the failing status test**

```php
<?php

// tests/Feature/Dashboard/WeddingDocumentStatusTest.php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingDocumentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_update_persists_and_sets_completed_at(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/n1/status', ['status' => 'beres'])
            ->assertSuccessful();

        $row = $plan->weddingDocuments()->where('key', 'n1')->first();
        $this->assertSame('beres', $row->status->value);
        $this->assertNotNull($row->completed_at);
    }

    public function test_unknown_key_is_rejected(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/not_a_real_key/status', ['status' => 'beres'])
            ->assertStatus(404);
    }
}
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --filter=WeddingDocument`
Expected: FAIL — routes/controller missing (404/500).

- [ ] **Step 4: Create the controller (context + data + status; file methods added in Task 7)**

```php
<?php

// app/Http/Controllers/Dashboard/WeddingDocumentController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\WeddingDocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\WeddingPlan;
use App\Services\WeddingDocumentService;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeddingDocumentController extends Controller
{
    public function __construct(private readonly WeddingDocumentService $service) {}

    public function data(): JsonResponse
    {
        $plan = $this->plan();

        return response()->json([
            'path'      => $plan->document_path,
            'flags'     => $plan->document_flags ?? (object) [],
            'documents' => $this->service->buildView($plan),
        ]);
    }

    public function context(Request $request): JsonResponse
    {
        $flagKeys = config('wedding_documents.flags');

        $data = $request->validate([
            'path'              => ['nullable', 'in:kua,sipil'],
            'flags'             => ['sometimes', 'array'],
            'flags.*'           => ['boolean'],
        ]);

        $plan = $this->plan();
        $flags = collect($data['flags'] ?? [])
            ->only($flagKeys)
            ->map(fn ($v) => (bool) $v)
            ->all();

        $plan->update([
            'document_path'  => $data['path'] ?? $plan->document_path,
            'document_flags' => array_merge($plan->document_flags ?? [], $flags),
        ]);

        return response()->json(['ok' => true]);
    }

    public function status(Request $request, string $key): JsonResponse
    {
        abort_unless($this->service->isValidKey($key), 404);

        $data = $request->validate([
            'status' => ['required', 'in:belum,proses,beres'],
        ]);

        $plan = $this->plan();
        $row  = $this->service->rowFor($plan, $key);
        $row->update([
            'status'       => $data['status'],
            'completed_at' => $data['status'] === WeddingDocumentStatus::Beres->value ? now() : null,
        ]);

        return response()->json(['ok' => true]);
    }

    private function plan(): WeddingPlan
    {
        return WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);
    }
}
```

> Note: `EffectiveUser::resolve()->id` is exactly how `ChecklistController::resolveOrCreatePlan()`
> (line 333) obtains the acting couple account. `use App\Support\EffectiveUser;` is required.

- [ ] **Step 5: Add routes inside the `dashboard.` group**

In `routes/web.php`, after the Checklist block (after line 290, before the group-closing `});` at line 291), add:

```php
    // ── Dokumen Nikah ──────────────────────────────────────────────────────
    Route::get(   '/documents/data',            [WeddingDocumentController::class, 'data'])->name('documents.data');
    Route::patch( '/documents/context',         [WeddingDocumentController::class, 'context'])->name('documents.context');
    Route::patch( '/documents/{key}/status',    [WeddingDocumentController::class, 'status'])->name('documents.status');
```

Add the import near the other `use App\Http\Controllers\Dashboard\...` lines at the top of `routes/web.php`:

```php
use App\Http\Controllers\Dashboard\WeddingDocumentController;
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=WeddingDocument`
Expected: PASS (catalog 3 + context 3 + status 2 = 8 tests).

- [ ] **Step 7: Commit**

```bash
rtk git add app/Http/Controllers/Dashboard/WeddingDocumentController.php routes/web.php tests/Feature/Dashboard/WeddingDocumentContextTest.php tests/Feature/Dashboard/WeddingDocumentStatusTest.php
rtk git commit -m "feat(documents): context + status endpoints"
```

---

## Task 7: File upload, delete, and signed serve

**Files:**
- Modify: `app/Http/Controllers/Dashboard/WeddingDocumentController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Dashboard/WeddingDocumentFileTest.php`

Files go on the private `local` disk under `wedding-documents/{planId}/{key}.{ext}`. They are served only through a signed, owner-checked route — never public.

- [ ] **Step 1: Write the failing file test**

```php
<?php

// tests/Feature/Dashboard/WeddingDocumentFileTest.php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WeddingDocumentFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_stores_file_privately_and_marks_row(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->postJson('/dashboard/documents/n1/file', [
                'file' => UploadedFile::fake()->create('n1.pdf', 200, 'application/pdf'),
            ])
            ->assertSuccessful();

        $row = $plan->weddingDocuments()->where('key', 'n1')->first();
        $this->assertNotNull($row->file_path);
        Storage::disk('local')->assertExists($row->file_path);
    }

    public function test_oversized_or_wrong_type_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->postJson('/dashboard/documents/n1/file', [
                'file' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
            ])
            ->assertStatus(422);
    }

    public function test_serve_route_rejects_non_owner(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan  = WeddingPlan::firstOrCreate(['user_id' => $owner->id]);
        $plan->update(['document_path' => 'kua']);
        $this->actingAs($owner)->postJson('/dashboard/documents/n1/file', [
            'file' => UploadedFile::fake()->create('n1.pdf', 100, 'application/pdf'),
        ])->assertSuccessful();

        $signed = URL::signedRoute('dashboard.documents.file.show', ['key' => 'n1', 'plan' => $plan->id]);

        $intruder = User::factory()->create(['onboarding_completed_at' => now()]);
        $this->actingAs($intruder)->get($signed)->assertForbidden();
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=WeddingDocumentFileTest`
Expected: FAIL — file routes/methods missing.

- [ ] **Step 3: Add file methods to the controller**

Add these `use` imports at the top of `WeddingDocumentController.php`:

```php
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
```

Add these methods to the class:

```php
    public function storeFile(Request $request, string $key): JsonResponse
    {
        abort_unless($this->service->isValidKey($key), 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $plan = $this->plan();
        $row  = $this->service->rowFor($plan, $key);

        // Replace any previous file for this key.
        if ($row->file_path) {
            Storage::disk('local')->delete($row->file_path);
        }

        $ext  = $request->file('file')->extension();
        $path = $request->file('file')->storeAs(
            "wedding-documents/{$plan->id}", "{$key}.{$ext}", 'local'
        );

        $row->update(['file_path' => $path]);

        return response()->json(['ok' => true, 'has_file' => true]);
    }

    public function destroyFile(string $key): JsonResponse
    {
        abort_unless($this->service->isValidKey($key), 404);

        $plan = $this->plan();
        $row  = $this->service->rowFor($plan, $key);

        if ($row->file_path) {
            Storage::disk('local')->delete($row->file_path);
            $row->update(['file_path' => null]);
        }

        return response()->json(['ok' => true, 'has_file' => false]);
    }

    public function showFile(Request $request, string $key, string $plan): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($this->service->isValidKey($key), 404);

        // Owner check: signed plan id must belong to the acting user.
        $resolved = $this->plan();
        abort_unless($resolved->id === $plan, 403);

        $row = $resolved->weddingDocuments()->where('key', $key)->firstOrFail();
        abort_if($row->file_path === null || ! Storage::disk('local')->exists($row->file_path), 404);

        return Storage::disk('local')->response($row->file_path);
    }
```

- [ ] **Step 4: Add the file routes**

In `routes/web.php`, under the Dokumen block from Task 6, add:

```php
    Route::post(  '/documents/{key}/file',      [WeddingDocumentController::class, 'storeFile'])->name('documents.file.store');
    Route::delete('/documents/{key}/file',      [WeddingDocumentController::class, 'destroyFile'])->name('documents.file.destroy');
    Route::get(   '/documents/{key}/file/{plan}',[WeddingDocumentController::class, 'showFile'])->name('documents.file.show');
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=WeddingDocumentFileTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Run the full document suite**

Run: `php artisan test --filter=WeddingDocument`
Expected: PASS (11 tests total).

- [ ] **Step 7: Commit**

```bash
rtk git add app/Http/Controllers/Dashboard/WeddingDocumentController.php routes/web.php tests/Feature/Dashboard/WeddingDocumentFileTest.php
rtk git commit -m "feat(documents): private file upload/delete/signed serve"
```

---

## Task 8: i18n keys

**Files:**
- Modify: `lang/id.json`
- Modify: `lang/en.json`

Keys live under `dashboard.documents.*` (sibling of `dashboard.checklist`). The exact parent nesting must match the existing `dashboard.checklist` block — open the file and add `documents` next to `checklist`.

- [ ] **Step 1: Add Indonesian keys**

In `lang/id.json`, inside the same `dashboard` object that contains `"checklist": { ... }`, add a sibling key:

```json
"documents": {
    "tabTugas": "Tugas",
    "tabDokumen": "Dokumen",
    "title": "Dokumen Nikah",
    "progress": "{done}/{total} dokumen beres",
    "choosePath": "Pilih jalur pengurusan dulu",
    "pathKua": "Islam / KUA",
    "pathSipil": "Sipil / Disdukcapil",
    "flags": {
        "beda_domisili": "Beda domisili",
        "under21": "Usia < 21",
        "under19": "Usia < 19",
        "widowed": "Duda / janda",
        "tni_polri": "TNI / Polri / PNS",
        "late_register": "Daftar < 10 hari"
    },
    "status": { "belum": "Belum", "proses": "Proses", "beres": "Beres" },
    "required": "Wajib",
    "optional": "Jika perlu",
    "upload": "Unggah",
    "replace": "Ganti",
    "remove": "Hapus",
    "guideWhere": "Di mana urus",
    "guideReq": "Syarat",
    "fileError": "Gagal unggah. Coba lagi (JPG/PNG/PDF, maks 5 MB)."
}
```

- [ ] **Step 2: Add English keys**

In `lang/en.json`, inside the matching `dashboard` object, add:

```json
"documents": {
    "tabTugas": "Tasks",
    "tabDokumen": "Documents",
    "title": "Marriage Documents",
    "progress": "{done}/{total} documents done",
    "choosePath": "Choose your registration path first",
    "pathKua": "Muslim / KUA",
    "pathSipil": "Civil / Disdukcapil",
    "flags": {
        "beda_domisili": "Different domicile",
        "under21": "Age < 21",
        "under19": "Age < 19",
        "widowed": "Widow / widower",
        "tni_polri": "TNI / Police / civil servant",
        "late_register": "Registering < 10 days"
    },
    "status": { "belum": "Not started", "proses": "In progress", "beres": "Done" },
    "required": "Required",
    "optional": "If needed",
    "upload": "Upload",
    "replace": "Replace",
    "remove": "Remove",
    "guideWhere": "Where to get it",
    "guideReq": "Requirements",
    "fileError": "Upload failed. Try again (JPG/PNG/PDF, max 5 MB)."
}
```

- [ ] **Step 3: Verify JSON is valid**

Run: `php -r "json_decode(file_get_contents('lang/id.json'), true) ?: exit(1); json_decode(file_get_contents('lang/en.json'), true) ?: exit(1); echo 'ok';"`
Expected: prints `ok` (both files parse).

- [ ] **Step 4: Commit**

```bash
rtk git add lang/id.json lang/en.json
rtk git commit -m "feat(documents): i18n keys for Dokumen tab"
```

---

## Task 9: DocumentCard component

**Files:**
- Create: `resources/js/Components/dashboard/documents/DocumentCard.vue`

One document row: title + required badge, status segmented control, upload/replace/remove, and an expandable guidance panel. Emits `status` and `file` events; the parent owns the network calls.

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  doc: { type: Object, required: true }, // { key,label,required,guidance,status,has_file,note }
});
const emit = defineEmits(['status', 'upload', 'remove']);
const { t } = useLocale();

const open = ref(false);
const fileInput = ref(null);
const statuses = ['belum', 'proses', 'beres'];

function pickFile() { fileInput.value?.click(); }
function onFile(e) {
  const f = e.target.files?.[0];
  if (f) emit('upload', { key: props.doc.key, file: f });
  e.target.value = '';
}
</script>

<template>
  <div class="rounded-2xl border p-4" style="border-color:#D8DFD2; background:#FFFFFF;">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-[14px] font-semibold" style="color:#1F2A2E;">{{ doc.label }}</p>
        <span class="text-[11px] font-medium"
              :style="doc.required ? 'color:#B4541F;' : 'color:#6C7A75;'">
          {{ doc.required ? t('dashboard.documents.required') : t('dashboard.documents.optional') }}
        </span>
      </div>
      <div class="inline-flex gap-0.5 p-[3px] rounded-full shrink-0"
           style="background:#F6F8F3; border:1px solid #D8DFD2;">
        <button v-for="s in statuses" :key="s" type="button"
                @click="emit('status', { key: doc.key, status: s })"
                class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors"
                :style="doc.status === s ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
          {{ t(`dashboard.documents.status.${s}`) }}
        </button>
      </div>
    </div>

    <div class="mt-3 flex items-center gap-2">
      <input ref="fileInput" type="file" class="hidden"
             accept=".jpg,.jpeg,.png,.pdf" @change="onFile" />
      <button type="button" @click="pickFile"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold"
              style="background:#EEF3E9; color:#2F4A33;">
        {{ doc.has_file ? t('dashboard.documents.replace') : t('dashboard.documents.upload') }}
      </button>
      <button v-if="doc.has_file" type="button" @click="emit('remove', { key: doc.key })"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold"
              style="background:#FBECEC; color:#9B2C2C;">
        {{ t('dashboard.documents.remove') }}
      </button>
      <button type="button" @click="open = !open"
              class="ml-auto text-[12px] font-medium underline" style="color:#6C7A75;">
        {{ t('dashboard.documents.guideWhere') }}
      </button>
    </div>

    <div v-if="open" class="mt-3 rounded-xl p-3 text-[12.5px] leading-relaxed"
         style="background:#F6F8F3; color:#3A4742;">
      <p><strong>{{ t('dashboard.documents.guideWhere') }}:</strong> {{ doc.guidance.where }}</p>
      <p class="mt-1"><strong>{{ t('dashboard.documents.guideReq') }}:</strong> {{ doc.guidance.requirements }}</p>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/dashboard/documents/DocumentCard.vue
rtk git commit -m "feat(documents): DocumentCard component"
```

---

## Task 10: DocumentContextPicker component

**Files:**
- Create: `resources/js/Components/dashboard/documents/DocumentContextPicker.vue`

Jalur toggle (kua/sipil) + condition flag chips. Emits `update` with `{ path, flags }`.

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  path:  { type: String, default: null },
  flags: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['update']);
const { t } = useLocale();

const FLAGS = ['beda_domisili', 'under21', 'under19', 'widowed', 'tni_polri', 'late_register'];

function setPath(p) { emit('update', { path: p, flags: props.flags }); }
function toggleFlag(f) {
  emit('update', { path: props.path, flags: { ...props.flags, [f]: !props.flags?.[f] } });
}
</script>

<template>
  <div class="rounded-2xl border p-4" style="border-color:#D8DFD2; background:#FFFFFF;">
    <div class="inline-flex gap-0.5 p-[3px] rounded-full" style="background:#F6F8F3; border:1px solid #D8DFD2;">
      <button type="button" @click="setPath('kua')"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold transition-colors"
              :style="path === 'kua' ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
        {{ t('dashboard.documents.pathKua') }}
      </button>
      <button type="button" @click="setPath('sipil')"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold transition-colors"
              :style="path === 'sipil' ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
        {{ t('dashboard.documents.pathSipil') }}
      </button>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
      <button v-for="f in FLAGS" :key="f" type="button" @click="toggleFlag(f)"
              class="px-3 py-1.5 rounded-full text-[11.5px] font-medium transition-colors"
              :style="flags?.[f] ? 'background:#EEF3E9; color:#2F4A33; border:1px solid #BBD0AE;'
                                 : 'background:#FFFFFF; color:#6C7A75; border:1px solid #D8DFD2;'">
        {{ t(`dashboard.documents.flags.${f}`) }}
      </button>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/dashboard/documents/DocumentContextPicker.vue
rtk git commit -m "feat(documents): DocumentContextPicker component"
```

---

## Task 11: DocumentsTab container

**Files:**
- Create: `resources/js/Components/dashboard/documents/DocumentsTab.vue`

Owns the data fetch + all network calls; composes picker, progress, and cards.

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useLocale } from '@/Composables/useLocale';
import DocumentContextPicker from './DocumentContextPicker.vue';
import DocumentCard from './DocumentCard.vue';

const { t } = useLocale();

const path = ref(null);
const flags = ref({});
const docs = ref([]);
const loading = ref(true);
const error = ref('');

const doneCount = computed(() => docs.value.filter(d => d.status === 'beres').length);

async function load() {
  loading.value = true;
  try {
    const { data } = await axios.get(route('dashboard.documents.data'));
    path.value = data.path;
    flags.value = data.flags && !Array.isArray(data.flags) ? data.flags : {};
    docs.value = data.documents ?? [];
  } finally {
    loading.value = false;
  }
}

async function updateContext({ path: p, flags: f }) {
  path.value = p; flags.value = f;
  await axios.patch(route('dashboard.documents.context'), { path: p, flags: f });
  await load();
}

async function setStatus({ key, status }) {
  const d = docs.value.find(x => x.key === key);
  if (d) d.status = status; // optimistic
  await axios.patch(route('dashboard.documents.status', key), { status });
}

async function upload({ key, file }) {
  error.value = '';
  const form = new FormData();
  form.append('file', file);
  try {
    await axios.post(route('dashboard.documents.file.store', key), form);
    const d = docs.value.find(x => x.key === key);
    if (d) d.has_file = true;
  } catch (e) {
    error.value = t('dashboard.documents.fileError');
  }
}

async function remove({ key }) {
  await axios.delete(route('dashboard.documents.file.destroy', key));
  const d = docs.value.find(x => x.key === key);
  if (d) d.has_file = false;
}

onMounted(load);
</script>

<template>
  <div class="space-y-4">
    <DocumentContextPicker :path="path" :flags="flags" @update="updateContext" />

    <p v-if="error" class="text-[12.5px] font-medium" style="color:#9B2C2C;">{{ error }}</p>

    <template v-if="path">
      <p class="text-[13px] font-semibold" style="color:#2F4A33;">
        {{ t('dashboard.documents.progress', { done: doneCount, total: docs.length }) }}
      </p>
      <div class="space-y-3">
        <DocumentCard v-for="d in docs" :key="d.key" :doc="d"
                      @status="setStatus" @upload="upload" @remove="remove" />
      </div>
    </template>

    <p v-else-if="!loading" class="text-[13px]" style="color:#6C7A75;">
      {{ t('dashboard.documents.choosePath') }}
    </p>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/dashboard/documents/DocumentsTab.vue
rtk git commit -m "feat(documents): DocumentsTab container"
```

---

## Task 12: Wire the tab into the Wedding Planner page

**Files:**
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue`

Add a `Tugas | Dokumen` segmented switch driven by `?tab`. When `dokumen`, render `DocumentsTab`; otherwise the existing checklist UI is untouched.

- [ ] **Step 1: Import DocumentsTab and add tab state**

In the `<script setup>` of `Index.vue`, add the import near the other component imports (around line 17):

```js
import DocumentsTab from '@/Components/dashboard/documents/DocumentsTab.vue';
```

Add tab state (after the existing `view`/`activeChip` refs around line 53):

```js
// ── Planner tab: 'tugas' | 'dokumen' (deep-linked via ?tab) ──────────────
const activeTab = ref(new URLSearchParams(window.location.search).get('tab') === 'dokumen' ? 'dokumen' : 'tugas');

function setTab(tab) {
    activeTab.value = tab;
    const url = new URL(window.location.href);
    if (tab === 'dokumen') url.searchParams.set('tab', 'dokumen');
    else url.searchParams.delete('tab');
    window.history.replaceState({}, '', url);
}
```

- [ ] **Step 2: Add the segmented switch + conditional render in the template**

In the template, add the switch at the top of the page content (just inside the main content wrapper, before the existing checklist hero). Use the existing layout container so spacing matches:

```vue
<div class="mb-4 inline-flex gap-0.5 p-[3px] rounded-full"
     style="background:#F6F8F3; border:1px solid #D8DFD2;">
  <button type="button" @click="setTab('tugas')"
          class="px-4 py-1.5 rounded-full text-[12px] font-semibold transition-colors"
          :style="activeTab === 'tugas' ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
    {{ t('dashboard.documents.tabTugas') }}
  </button>
  <button type="button" @click="setTab('dokumen')"
          class="px-4 py-1.5 rounded-full text-[12px] font-semibold transition-colors"
          :style="activeTab === 'dokumen' ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
    {{ t('dashboard.documents.tabDokumen') }}
  </button>
</div>

<DocumentsTab v-if="activeTab === 'dokumen'" />
```

Wrap the existing checklist body (hero + stat strip + lists/kanban + rails — everything that currently renders the task UI) so it only shows on the Tugas tab. The simplest non-invasive way: add `v-show="activeTab === 'tugas'"` to the existing top-level wrapper element of the checklist body. Find that wrapper (the element directly containing `ChecklistProgressHero`) and add the attribute.

- [ ] **Step 3: Build the frontend**

Run: `npm run build`
Expected: build succeeds, no Vue compile errors referencing the new components.

- [ ] **Step 4: Manual verification**

Open the Wedding Planner page. Verify:
- Default shows `Tugas` (existing checklist), tab switch visible.
- Click `Dokumen` → URL gains `?tab=dokumen`, picker appears with "choose path" empty-state.
- Pick `Islam / KUA` → catalog rows render; toggle `Beda domisili` → "Surat Rekomendasi / Numpang Nikah" appears.
- Set a status to `Beres` → progress count updates.
- Upload a PDF → button switches to `Ganti` + `Hapus`.
- Reload with `?tab=dokumen` → still on Dokumen tab with saved state.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Checklist/Index.vue
rtk git commit -m "feat(documents): Tugas|Dokumen tab switch in Wedding Planner"
```

---

## Task 13: Dashboard entry card

**Files:**
- Modify: `resources/js/Components/dashboard/widgets/ChecklistCard.vue`

Add a secondary link/CTA "Dokumen Nikah" that routes to the Planner with `?tab=dokumen`, so the seasonal job is discoverable from the dashboard.

- [ ] **Step 1: Read the current card to match its markup**

Run: open `resources/js/Components/dashboard/widgets/ChecklistCard.vue` and locate the primary link to the checklist (it uses `route('dashboard.checklist.index')`).

- [ ] **Step 2: Add the Dokumen link**

Next to the existing checklist CTA, add a secondary link mirroring the existing link's classes (copy the existing `<Link>`/`<a>`'s class attribute verbatim so styling matches), pointing to:

```vue
<Link :href="route('dashboard.checklist.index') + '?tab=dokumen'"
      class="<same classes as the existing checklist CTA link>">
  {{ t('dashboard.documents.title') }}
</Link>
```

If `t` / `Link` are not already imported in this component, add:

```js
import { Link } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
```

- [ ] **Step 3: Build and verify**

Run: `npm run build`
Expected: build succeeds. On the dashboard, the card now shows a "Dokumen Nikah" link that opens the Planner on the Dokumen tab.

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/ChecklistCard.vue
rtk git commit -m "feat(documents): dashboard entry to Dokumen tab"
```

---

## Task 14: Full suite + final verification

- [ ] **Step 1: Run the full backend test suite**

Run: `php artisan test --filter=WeddingDocument`
Expected: PASS (11 tests).

- [ ] **Step 2: Run the broader checklist/planner suite to confirm no regression**

Run: `php artisan test --filter=Checklist`
Expected: PASS (unchanged).

- [ ] **Step 3: Production build**

Run: `npm run build`
Expected: succeeds with no errors.

- [ ] **Step 4: Final manual pass**

Re-run the Task 12 Step 4 checklist end-to-end on a clean reload, plus:
- Switch jalur from KUA to Sipil → KUA-only rows disappear, Sipil rows (pemberkatan, saksi) appear.
- Remove an uploaded file → row returns to upload-only state.

---

## Self-Review Notes (for the implementing engineer)

- **Spec coverage:** placement/tab (T12), tiap-dokumen status+upload+panduan (T9), jalur preset + flags + beda-domisili (T2/T5/T10), verified catalog (T2), private file + signed route (T7), config-as-source-of-truth (T2), no religion column (T3 stores `document_path` only), dashboard entry (T13), anti-halu catalog test (T2).
- **EffectiveUser:** Task 6's `plan()` uses `EffectiveUser::resolve()->id` — verified against `ChecklistController::resolveOrCreatePlan()` (line 333).
- **Index.vue wrapping (T12 Step 2):** the only judgment call — identify the existing checklist body wrapper and gate it with `v-show="activeTab === 'tugas'"`. Do not delete or restructure existing task UI.
