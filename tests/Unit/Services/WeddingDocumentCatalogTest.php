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
