<?php

declare(strict_types=1);

namespace App\Support;

class VendorCategories
{
    /**
     * Fixed category catalog (single source of truth).
     *
     * @return array<int, array{key: string, label: string, important: bool, why: string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'venue',             'label' => 'Venue',             'important' => true,  'why' => 'Lokasi & tanggal acara, fondasi semua vendor lain'],
            ['key' => 'catering',          'label' => 'Catering',          'important' => true,  'why' => 'Konsumsi tamu, porsi terbesar dari anggaran'],
            ['key' => 'foto_video',        'label' => 'Foto & Video',      'important' => true,  'why' => 'Dokumentasi momen yang tak terulang'],
            ['key' => 'dekorasi',          'label' => 'Dekorasi',          'important' => true,  'why' => 'Suasana & estetika pelaminan dan venue'],
            ['key' => 'mua',               'label' => 'MUA',               'important' => true,  'why' => 'Tata rias pengantin untuk akad & resepsi'],
            ['key' => 'busana',            'label' => 'Busana',            'important' => true,  'why' => 'Gaun & jas pengantin, sering perlu fitting jauh hari'],
            ['key' => 'mc',                'label' => 'MC',                'important' => true,  'why' => 'Pemandu acara agar rundown berjalan lancar'],
            ['key' => 'wedding_organizer', 'label' => 'Wedding Organizer', 'important' => true,  'why' => 'Bantu koordinasi H-7 sampai hari H'],
            ['key' => 'sound_system',      'label' => 'Sound System',      'important' => true,  'why' => 'Penting untuk akad & resepsi'],
            ['key' => 'mobil_pengantin',   'label' => 'Mobil Pengantin',   'important' => true,  'why' => 'Antar-jemput keluarga inti'],
            ['key' => 'hiburan',           'label' => 'Hiburan',           'important' => false, 'why' => 'Band, organ tunggal, atau pengisi acara'],
            ['key' => 'souvenir',          'label' => 'Souvenir',          'important' => false, 'why' => 'Kenang-kenangan untuk tamu'],
            ['key' => 'lainnya',           'label' => 'Lainnya',           'important' => false, 'why' => 'Kebutuhan vendor lain di luar kategori utama'],
        ];
    }

    /**
     * All category keys (for validation `in:`).
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_map(static fn (array $c): string => $c['key'], self::all());
    }

    /**
     * Important categories whose key is NOT in $presentKeys.
     *
     * @param  array<int, string>  $presentKeys
     * @return array<int, array{key: string, label: string, why: string}>
     */
    public static function gap(array $presentKeys): array
    {
        $present = array_flip($presentKeys);

        $gap = [];
        foreach (self::all() as $c) {
            if ($c['important'] && ! isset($present[$c['key']])) {
                $gap[] = [
                    'key'   => $c['key'],
                    'label' => $c['label'],
                    'why'   => $c['why'],
                ];
            }
        }

        return $gap;
    }

    public static function label(string $key): ?string
    {
        foreach (self::all() as $c) {
            if ($c['key'] === $key) {
                return $c['label'];
            }
        }

        return null;
    }

    /**
     * Map a vendor category key to the budget category slug it should land in
     * (see config/budget_categories.php). Falls back to 'lainnya'.
     */
    public static function budgetSlug(string $key): string
    {
        return [
            'venue'             => 'venue',
            'catering'          => 'catering',
            'foto_video'        => 'dokumentasi',
            'dekorasi'          => 'dekorasi',
            'mua'               => 'makeup-beauty',
            'busana'            => 'busana',
            'mc'                => 'hiburan',
            'wedding_organizer' => 'lainnya',
            'sound_system'      => 'hiburan',
            'mobil_pengantin'   => 'transportasi',
            'hiburan'           => 'hiburan',
            'souvenir'          => 'souvenir',
            'lainnya'           => 'lainnya',
        ][$key] ?? 'lainnya';
    }
}
