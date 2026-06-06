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
