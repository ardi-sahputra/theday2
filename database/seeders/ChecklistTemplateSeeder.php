<?php

// database/seeders/ChecklistTemplateSeeder.php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChecklistTemplateSeeder extends Seeder
{
    /**
     * day_offset = days relative to event_date.
     * Negative = before event. Null = no time reference.
     */
    public function run(): void
    {
        $now       = now();
        $templates = [
            // ── Administrasi ──────────────────────────────────────────────
            [
                'name'       => 'Buku Nikah',
                'category'   => 'administrasi',
                'title'      => 'Urus dokumen & buku nikah',
                'description'=> 'Siapkan KTP, KK, akta kelahiran, dan dokumen yang diperlukan KUA/catatan sipil.',
                'day_offset' => -180,
                'priority'   => 'high',
                'sort_order' => 10,
            ],
            [
                'name'       => 'Surat izin orang tua',
                'category'   => 'administrasi',
                'title'      => 'Siapkan surat izin orang tua (jika dibutuhkan)',
                'description'=> null,
                'day_offset' => -150,
                'priority'   => 'medium',
                'sort_order' => 20,
            ],
            [
                'name'       => 'Daftar ke KUA',
                'category'   => 'administrasi',
                'title'      => 'Daftar & konfirmasi jadwal ke KUA',
                'description'=> null,
                'day_offset' => -90,
                'priority'   => 'high',
                'sort_order' => 30,
            ],

            // ── Venue ────────────────────────────────────────────────────
            [
                'name'       => 'Survei venue',
                'category'   => 'venue',
                'title'      => 'Survei dan bandingkan venue resepsi',
                'description'=> null,
                'day_offset' => -270,
                'priority'   => 'high',
                'sort_order' => 40,
            ],
            [
                'name'       => 'Booking venue',
                'category'   => 'venue',
                'title'      => 'Booking venue resepsi & bayar DP',
                'description'=> null,
                'day_offset' => -240,
                'priority'   => 'high',
                'sort_order' => 50,
            ],
            [
                'name'         => 'Konfirmasi venue',
                'category'     => 'venue',
                'wedding_types'=> ['akad-resepsi', 'destination'],
                'title'        => 'Konfirmasi ulang detail venue (layout, catering, parkir)',
                'description'  => null,
                'day_offset'   => -30,
                'priority'     => 'medium',
                'sort_order'   => 60,
            ],

            // ── Vendor ───────────────────────────────────────────────────
            [
                'name'       => 'Pilih katering',
                'category'   => 'vendor',
                'title'      => 'Survei dan pilih katering',
                'description'=> 'Lakukan food tasting sebelum memutuskan.',
                'day_offset' => -210,
                'priority'   => 'high',
                'sort_order' => 70,
            ],
            [
                'name'         => 'Pilih MC',
                'category'     => 'vendor',
                'wedding_types'=> ['akad-resepsi', 'destination'],
                'title'        => 'Tentukan MC acara',
                'description'  => null,
                'day_offset'   => -150,
                'priority'     => 'medium',
                'sort_order'   => 80,
            ],
            [
                'name'         => 'Pilih dekorasi',
                'category'     => 'vendor',
                'wedding_types'=> ['akad-resepsi', 'destination'],
                'title'        => 'Pilih vendor dekorasi & konsultasi konsep',
                'description'  => null,
                'day_offset'   => -180,
                'priority'     => 'high',
                'sort_order'   => 90,
            ],
            [
                'name'       => 'Konfirmasi vendor',
                'category'   => 'vendor',
                'title'      => 'Konfirmasi semua vendor seminggu sebelum hari H',
                'description'=> 'Pastikan kontak, jadwal, dan detail teknis sudah disetujui.',
                'day_offset' => -7,
                'priority'   => 'high',
                'sort_order' => 100,
            ],

            // ── Busana ───────────────────────────────────────────────────
            [
                'name'       => 'Fitting baju pengantin',
                'category'   => 'busana',
                'title'      => 'Fitting pertama baju pengantin',
                'description'=> null,
                'day_offset' => -120,
                'priority'   => 'high',
                'sort_order' => 110,
            ],
            [
                'name'       => 'Fitting final',
                'category'   => 'busana',
                'title'      => 'Fitting final & pengambilan baju',
                'description'=> null,
                'day_offset' => -14,
                'priority'   => 'high',
                'sort_order' => 120,
            ],

            // ── Undangan ─────────────────────────────────────────────────
            [
                'name'       => 'Buat undangan digital',
                'category'   => 'undangan',
                'title'      => 'Buat & publish undangan digital',
                'description'=> null,
                'day_offset' => -60,
                'priority'   => 'medium',
                'sort_order' => 130,
            ],
            [
                'name'         => 'Cetak undangan fisik',
                'category'     => 'undangan',
                'wedding_types'=> ['akad-resepsi'],
                'title'        => 'Order cetak undangan fisik',
                'description'  => null,
                'day_offset'   => -45,
                'priority'     => 'medium',
                'sort_order'   => 140,
            ],
            [
                'name'       => 'Sebar undangan',
                'category'   => 'undangan',
                'title'      => 'Kirim & sebar undangan ke semua tamu',
                'description'=> null,
                'day_offset' => -30,
                'priority'   => 'medium',
                'sort_order' => 150,
            ],

            // ── Tamu ─────────────────────────────────────────────────────
            [
                'name'       => 'Buat daftar tamu',
                'category'   => 'tamu',
                'title'      => 'Buat daftar tamu undangan',
                'description'=> 'Tentukan kuota dan susun daftar dari kedua pihak keluarga.',
                'day_offset' => -90,
                'priority'   => 'medium',
                'sort_order' => 160,
            ],
            [
                'name'       => 'Konfirmasi kehadiran',
                'category'   => 'tamu',
                'title'      => 'Rekap konfirmasi kehadiran tamu (RSVP)',
                'description'=> null,
                'day_offset' => -7,
                'priority'   => 'low',
                'sort_order' => 170,
            ],

            // ── Acara ─────────────────────────────────────────────────────
            [
                'name'       => 'Susun rundown',
                'category'   => 'acara',
                'title'      => 'Susun rundown detail hari H',
                'description'=> 'Koordinasikan dengan MC, WO, dan pihak venue.',
                'day_offset' => -30,
                'priority'   => 'high',
                'sort_order' => 180,
            ],
            [
                'name'         => 'Gladi bersih',
                'category'     => 'acara',
                'wedding_types'=> ['akad-resepsi', 'destination'],
                'title'        => 'Lakukan gladi bersih prosesi',
                'description'  => null,
                'day_offset'   => -1,
                'priority'     => 'medium',
                'sort_order'   => 190,
            ],

            // ── Dokumentasi ──────────────────────────────────────────────
            [
                'name'       => 'Pilih fotografer',
                'category'   => 'dokumentasi',
                'title'      => 'Pilih & booking fotografer/videografer',
                'description'=> 'Tinjau portofolio dan diskusikan konsep foto.',
                'day_offset' => -180,
                'priority'   => 'high',
                'sort_order' => 200,
            ],
            [
                'name'       => 'Prewedding',
                'category'   => 'dokumentasi',
                'title'      => 'Sesi foto prewedding',
                'description'=> null,
                'day_offset' => -60,
                'priority'   => 'medium',
                'sort_order' => 210,
            ],
            [
                'name'       => 'Brief fotografer',
                'category'   => 'dokumentasi',
                'title'      => 'Brief fotografer: shot list & rundown',
                'description'=> null,
                'day_offset' => -7,
                'priority'   => 'medium',
                'sort_order' => 220,
            ],

            // ── Destination only ─────────────────────────────────────────
            [
                'name'         => 'Izin & legalitas lokasi',
                'category'     => 'administrasi',
                'wedding_types'=> ['destination'],
                'title'        => 'Urus izin & legalitas venue (luar kota/negeri)',
                'description'  => 'Cek persyaratan dokumen, izin acara, dan legalitas pernikahan di lokasi tujuan.',
                'day_offset'   => -180,
                'priority'     => 'high',
                'sort_order'   => 35,
            ],
            [
                'name'         => 'Akomodasi tamu',
                'category'     => 'venue',
                'wedding_types'=> ['destination'],
                'title'        => 'Atur akomodasi & penginapan tamu',
                'description'  => 'Booking blok kamar hotel/villa dan info penginapan untuk tamu dari luar kota.',
                'day_offset'   => -120,
                'priority'     => 'high',
                'sort_order'   => 55,
            ],
            [
                'name'         => 'Transport destination',
                'category'     => 'vendor',
                'wedding_types'=> ['destination'],
                'title'        => 'Atur transport tamu & vendor ke lokasi',
                'description'  => 'Koordinasi transport (shuttle/sewa) untuk tamu dan pengiriman kebutuhan vendor.',
                'day_offset'   => -90,
                'priority'     => 'medium',
                'sort_order'   => 105,
            ],
        ];

        foreach ($templates as $template) {
            $exists = DB::table('checklist_templates')
                ->where('name', $template['name'])
                ->where('category', $template['category'])
                ->exists();

            $payload = [
                'title'         => $template['title'],
                'description'   => $template['description'],
                'wedding_types' => isset($template['wedding_types'])
                    ? json_encode($template['wedding_types'])
                    : null,
                'day_offset'    => $template['day_offset'],
                'priority'      => $template['priority'],
                'is_active'     => true,
                'sort_order'    => $template['sort_order'],
                'updated_at'    => $now,
            ];

            if ($exists) {
                DB::table('checklist_templates')
                    ->where('name', $template['name'])
                    ->where('category', $template['category'])
                    ->update($payload);
            } else {
                DB::table('checklist_templates')->insert(array_merge($payload, [
                    'id'         => Str::uuid()->toString(),
                    'name'       => $template['name'],
                    'category'   => $template['category'],
                    'created_at' => $now,
                ]));
            }
        }
    }
}
