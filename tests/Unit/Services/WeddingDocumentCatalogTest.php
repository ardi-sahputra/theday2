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
}
