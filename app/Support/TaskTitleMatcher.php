<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Fuzzy duplicate detection for checklist task titles.
 *
 * Exact string matching misses paraphrases — "Tentukan tanggal akad" vs
 * "Menetapkan tanggal pernikahan" are the same task to a human but differ as
 * strings, so an AI-generated list slips past an equality check and the couple
 * sees doubled tasks. This normalizes titles (lowercase, strip punctuation and
 * common Indonesian action verbs / stopwords) and compares the remaining token
 * sets, treating high overlap as a duplicate.
 */
final class TaskTitleMatcher
{
    /** Leading action verbs + glue words that carry no distinguishing meaning. */
    private const STOPWORDS = [
        'tentukan', 'menentukan', 'tetapkan', 'menetapkan', 'buat', 'membuat',
        'bikin', 'atur', 'mengatur', 'siapkan', 'menyiapkan', 'persiapkan',
        'pesan', 'memesan', 'booking', 'book', 'cari', 'mencari', 'pilih',
        'memilih', 'urus', 'mengurus', 'daftar', 'mendaftar', 'kirim',
        'mengirim', 'beli', 'membeli', 'lakukan', 'melakukan', 'sewa',
        'menyewa', 'rencanakan', 'merencanakan', 'finalisasi', 'konfirmasi',
        'dan', 'atau', 'untuk', 'yang', 'ke', 'di', 'dengan', 'serta', 'para',
        'acara', 'pernikahan', 'wedding', 'nikah',
    ];

    /** Jaccard similarity at/above this is treated as duplicate. */
    private const JACCARD_THRESHOLD = 0.6;

    /** Overlap relative to the smaller token set at/above this is duplicate. */
    private const CONTAINMENT_THRESHOLD = 0.8;

    /**
     * Significant, sorted, unique tokens of a title.
     *
     * @return array<int, string>
     */
    public static function tokens(string $title): array
    {
        $t = mb_strtolower(trim($title));
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t) ?? '';
        $words = preg_split('/\s+/', $t, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_filter(
            $words,
            fn (string $w): bool => mb_strlen($w) > 2 && ! in_array($w, self::STOPWORDS, true),
        );
        $words = array_values(array_unique($words));
        sort($words);

        return $words;
    }

    /**
     * Does $title duplicate any of $existingTitles (fuzzy)?
     *
     * @param  array<int, string>  $existingTitles
     */
    public static function isDuplicate(string $title, array $existingTitles): bool
    {
        $a = self::tokens($title);
        if ($a === []) {
            return false;
        }

        foreach ($existingTitles as $existing) {
            $b = self::tokens((string) $existing);
            if ($b === []) {
                continue;
            }

            $intersection = count(array_intersect($a, $b));
            if ($intersection === 0) {
                continue;
            }

            $union = count(array_unique(array_merge($a, $b)));
            $jaccard = $union > 0 ? $intersection / $union : 0.0;
            if ($jaccard >= self::JACCARD_THRESHOLD) {
                return true;
            }

            $containment = $intersection / min(count($a), count($b));
            if ($containment >= self::CONTAINMENT_THRESHOLD) {
                return true;
            }
        }

        return false;
    }
}
