<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use PHPUnit\Framework\TestCase;

/**
 * Guard test: enforces the spec rule that any queued job dispatched from a
 * request context must NOT read auth() at handle time. This test scans the
 * codebase for violations rather than instantiating a specific job, so it
 * keeps working as jobs come and go.
 */
class QueuedJobContextTest extends TestCase
{
    public function test_no_queueable_class_reads_auth(): void
    {
        // Compute app root from this file's location (tests/Feature/Couple/ -> 3 levels up)
        $appRoot = dirname(__DIR__, 3);
        $roots = [$appRoot . '/app/Jobs', $appRoot . '/app/Notifications', $appRoot . '/app/Mail'];
        $offenders = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) continue;
            $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($iter as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') continue;
                $body = file_get_contents($file->getPathname());
                $isQueueable = str_contains($body, 'ShouldQueue')
                    || str_contains($body, 'use Queueable');
                if (! $isQueueable) continue;
                if (preg_match('/\bauth\(\)|\bAuth::(user|id)\(/', $body)) {
                    $offenders[] = str_replace($appRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $offenders, 'Queueable classes must not read auth():' . PHP_EOL . implode(PHP_EOL, $offenders));
    }
}
