<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\CoupleToken;
use PHPUnit\Framework\TestCase;

class CoupleTokenTest extends TestCase
{
    public function test_generate_returns_64_char_hex(): void
    {
        $token = CoupleToken::generate();

        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function test_generate_returns_unique_tokens(): void
    {
        $tokens = array_map(fn () => CoupleToken::generate(), range(1, 100));

        $this->assertSame(100, count(array_unique($tokens)));
    }

    public function test_hash_is_deterministic_and_64_chars(): void
    {
        $token = 'deadbeef';
        $hash1 = CoupleToken::hash($token);
        $hash2 = CoupleToken::hash($token);

        $this->assertSame($hash1, $hash2);
        $this->assertSame(64, strlen($hash1));
    }

    public function test_hash_changes_when_token_changes(): void
    {
        $this->assertNotSame(
            CoupleToken::hash('abc'),
            CoupleToken::hash('xyz'),
        );
    }
}
