<?php

declare(strict_types=1);

return [
    'common' => [
        'tim_theday' => 'Tim TheDay',
    ],
    'flash' => [
        'purchase_error' => 'Pembayaran gagal diproses. Silakan coba lagi atau hubungi support.',
        'claim_success'  => 'Premium berhasil diaktivasi! Cek dashboard untuk detail.',
        'created'        => 'Gift code :code berhasil dibuat.',
        'deleted'        => 'Gift dihapus.',
        'cannot_delete'  => 'Hanya gift dengan status pending yang bisa dihapus.',
    ],
    'validation' => [
        'plan_invalid' => 'Plan tidak valid untuk gift.',
    ],
    'mail' => [
        'received_subject' => 'Kamu dapat gift premium TheDay!',
        'claimed_subject'  => 'Gift kamu sudah diklaim!',
        'received_heading' => 'Selamat! Kamu dapat gift premium 🎁',
        'received_greeting' => 'Halo,',
        'received_intro'   => 'mengirimkan kamu akses Premium :plan selama :days hari.',
        'received_cta'     => 'Klaim Gift Sekarang',
        'received_link_prefix' => 'Atau buka link berikut:',
        'received_expires' => 'Gift ini berlaku sampai :date. Setelah itu kode akan kadaluarsa.',
        'claimed_heading'  => 'Gift kamu sudah diklaim!',
        'claimed_greeting' => 'Halo :name,',
        'claimed_body'     => ':recipient (:email) baru saja mengklaim gift premium kamu pada :date.',
        'claimed_thanks'   => 'Terima kasih sudah menyebarkan kebahagiaan!',
    ],
];
