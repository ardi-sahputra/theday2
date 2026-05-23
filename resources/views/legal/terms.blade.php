@extends('layouts.legal')

@section('title', 'Syarat & Ketentuan')
@section('meta_description', 'Syarat dan Ketentuan penggunaan Theday — platform undangan pernikahan digital Indonesia.')
@section('breadcrumb', 'Syarat & Ketentuan')
@section('page_title', 'Syarat & Ketentuan')
@section('last_updated', '16 April 2026')

@section('toc')
    <a href="#pendahuluan"          class="toc-link">1. Pendahuluan</a>
    <a href="#akun-pengguna"        class="toc-link">2. Akun Pengguna</a>
    <a href="#layanan"              class="toc-link">3. Layanan yang Disediakan</a>
    <a href="#pembayaran"           class="toc-link">4. Pembayaran & Langganan</a>
    <a href="#konten-pengguna"      class="toc-link">5. Konten Pengguna</a>
    <a href="#hak-kekayaan"         class="toc-link">6. Hak Kekayaan Intelektual</a>
    <a href="#pembatasan"           class="toc-link">7. Pembatasan Tanggung Jawab</a>
    <a href="#penghentian"          class="toc-link">8. Penghentian Layanan</a>
    <a href="#hukum"                class="toc-link">9. Hukum yang Berlaku</a>
    <a href="#perubahan-syarat"     class="toc-link">10. Perubahan Syarat</a>
    <a href="#kontak"               class="toc-link">11. Kontak</a>
@endsection

@section('content')

{{-- 1 --}}
<section id="pendahuluan" data-section>
    <h2>1. Pendahuluan</h2>
    <p>
        Selamat datang di Theday. Dengan mendaftar atau menggunakan layanan kami, kamu menyetujui
        untuk terikat oleh Syarat & Ketentuan ini. Mohon baca dengan seksama sebelum menggunakan Theday.
    </p>
    <p>
        Jika kamu tidak menyetujui syarat ini, kamu tidak dapat menggunakan layanan Theday.
    </p>
    <ul>
        <li>Usia minimum untuk menggunakan Theday adalah <strong>17 tahun</strong></li>
        <li>Syarat ini berlaku sejak tanggal yang tercantum di bagian atas halaman ini</li>
        <li>Syarat ini dibuat dalam Bahasa Indonesia dan diatur oleh hukum Republik Indonesia</li>
    </ul>
</section>

{{-- 2 --}}
<section id="akun-pengguna" data-section>
    <h2>2. Akun Pengguna</h2>
    <ul>
        <li>
            <strong>Keamanan akun</strong> — Kamu bertanggung jawab penuh atas kerahasiaan password dan
            semua aktivitas yang terjadi di akunmu. Segera hubungi kami jika kamu mencurigai akunmu telah
            diakses oleh pihak yang tidak berwenang.
        </li>
        <li>
            <strong>Satu akun per pengguna</strong> — Setiap orang hanya boleh memiliki satu akun Theday.
            Mendaftarkan beberapa akun untuk menghindari batasan layanan tidak diizinkan.
        </li>
        <li>
            <strong>Informasi akun yang akurat</strong> — Kamu wajib memberikan informasi yang benar dan terkini
            saat mendaftar. Akun yang menggunakan informasi palsu dapat dihentikan.
        </li>
        <li>
            <strong>Penangguhan akun</strong> — Theday berhak menangguhkan atau menghentikan akun yang terbukti
            melanggar Syarat & Ketentuan ini, tanpa pemberitahuan sebelumnya jika diperlukan.
        </li>
    </ul>
</section>

{{-- 3 --}}
<section id="layanan" data-section>
    <h2>3. Layanan yang Disediakan</h2>
    <p>
        Theday adalah platform Software as a Service (SaaS) yang menyediakan alat untuk membuat,
        menyesuaikan, dan mendistribusikan undangan pernikahan digital secara online.
    </p>

    <h3>Paket Layanan</h3>
    <ul>
        <li>
            <strong>Paket Free</strong> — gratis selamanya, akses ke template dasar, dengan watermark Theday
        </li>
        <li>
            <strong>Paket Premium</strong> — tanpa watermark, upload musik sendiri, analitik lengkap,
            semua template premium, undangan tidak terbatas, prioritas dukungan
        </li>
    </ul>
    <p>
        Detail fitur setiap paket dapat berubah. Theday akan memberikan pemberitahuan kepada pengguna
        aktif jika ada perubahan signifikan pada fitur yang sudah mereka bayar.
    </p>

    <h3>Masa Aktif Undangan</h3>
    <p>
        Undangan yang kamu buat akan tetap aktif dan dapat diakses oleh tamu selama akunmu aktif dan
        masa langgananmu berlaku. Undangan dari akun Free akan tetap aktif selama akunmu tidak dihapus.
    </p>

    <h3>Ketersediaan Layanan</h3>
    <p>
        Kami berupaya keras menjaga Theday tetap online dan dapat diakses. Namun, layanan dapat mengalami
        gangguan sesekali karena pemeliharaan, pembaruan, atau kejadian di luar kendali kami.
        Kami tidak menjamin uptime 100%.
    </p>
</section>

{{-- 4 --}}
<section id="pembayaran" data-section>
    <h2>4. Pembayaran dan Langganan</h2>
    <ul>
        <li>
            <strong>Pemrosesan pembayaran</strong> — semua transaksi diproses melalui <strong>Midtrans</strong>,
            payment gateway berlisensi Bank Indonesia. Theday tidak menyimpan data kartu kreditmu.
        </li>
        <li>
            <strong>Harga</strong> — harga dapat berubah sewaktu-waktu, namun kami akan memberikan pemberitahuan
            setidaknya 14 hari sebelum perubahan harga berlaku.
        </li>
        <li>
            <strong>Masa aktif paket</strong> — paket berbayar berlaku selama 30 hari sejak tanggal pembayaran
            berhasil dikonfirmasi.
        </li>
        <li>
            <strong>Tidak ada perpanjangan otomatis</strong> — Theday menggunakan sistem pembayaran sekali bayar
            (one-time payment). Tidak ada langganan berulang otomatis.
        </li>
        <li>
            <strong>Garansi uang kembali</strong> — jika kamu tidak puas dalam <strong>7 hari pertama</strong>
            setelah pembayaran, kamu dapat mengajukan pengembalian dana penuh. Hubungi kami di
            <a href="mailto:hello@theday.id">hello@theday.id</a> dengan menyertakan bukti transaksi.
        </li>
        <li>
            <strong>Pembatalan</strong> — pengembalian dana tidak berlaku setelah periode 7 hari atau jika
            kamu telah menggunakan fitur premium secara ekstensif.
        </li>
    </ul>
</section>

{{-- 5 --}}
<section id="konten-pengguna" data-section>
    <h2>5. Konten Pengguna</h2>
    <p>
        Konten yang kamu unggah dan buat di Theday (foto, teks, musik, dll.) sepenuhnya menjadi
        tanggung jawabmu. Dengan menggunakan Theday, kamu menyatakan bahwa:
    </p>
    <ul>
        <li>Kamu memiliki hak untuk menggunakan semua konten yang kamu unggah</li>
        <li>Konten yang kamu unggah tidak melanggar hak cipta, hak privasi, atau hak hukum pihak lain</li>
        <li>Konten yang kamu unggah tidak bersifat ilegal, mengandung pornografi, SARA, kekerasan,
            atau hal-hal yang dilarang oleh hukum Indonesia</li>
    </ul>
    <p>
        Theday berhak menghapus konten yang melanggar ketentuan ini tanpa pemberitahuan terlebih dahulu,
        dan dapat menangguhkan akun yang berulang kali melanggar.
    </p>
    <p>
        Theday tidak bertanggung jawab atas pesan yang dikirimkan tamu melalui fitur Buku Tamu.
        Kamu dapat menghapus pesan yang tidak sesuai melalui panel dashboard undanganmu.
    </p>
</section>

{{-- 6 --}}
<section id="hak-kekayaan" data-section>
    <h2>6. Hak Kekayaan Intelektual</h2>
    <ul>
        <li>
            <strong>Template desain</strong> — semua template, desain antarmuka, dan aset visual Theday adalah
            milik Theday dan dilindungi oleh hak cipta. Kamu boleh menggunakannya untuk membuat undanganmu
            sendiri, namun tidak boleh mengkopi, menjual, atau mendistribusikannya.
        </li>
        <li>
            <strong>Konten kamu</strong> — teks, foto, dan konten lain yang kamu buat atau unggah tetap menjadi
            milikmu sepenuhnya. Theday tidak mengklaim kepemilikan atas konten yang kamu buat.
        </li>
        <li>
            <strong>Lisensi terbatas</strong> — dengan mengunggah konten ke Theday, kamu memberikan Theday
            lisensi terbatas, non-eksklusif, dan bebas royalti untuk menyimpan, menampilkan, dan
            memproses kontenmu semata-mata untuk keperluan menyediakan layanan kepadamu.
        </li>
        <li>
            <strong>Nama & merek Theday</strong> — nama "Theday", logo, dan merek terkait adalah milik Theday
            dan tidak boleh digunakan tanpa izin tertulis.
        </li>
    </ul>
</section>

{{-- 7 --}}
<section id="pembatasan" data-section>
    <h2>7. Pembatasan Tanggung Jawab</h2>
    <p>
        Theday menyediakan layanan "sebagaimana adanya" (<em>as is</em>). Sejauh diizinkan oleh hukum yang berlaku:
    </p>
    <ul>
        <li>
            Theday tidak bertanggung jawab atas kerugian tidak langsung, insidental, atau konsekuensial
            yang timbul dari penggunaan atau ketidakmampuan menggunakan layanan kami.
        </li>
        <li>
            Theday tidak bertanggung jawab atas kehilangan data akibat kejadian yang di luar kendali kami
            (force majeure, bencana alam, serangan siber oleh pihak ketiga).
        </li>
        <li>
            Theday tidak bertanggung jawab atas konten yang dikirimkan oleh tamu melalui fitur Buku Tamu
            atau RSVP undanganmu.
        </li>
        <li>
            Theday tidak menjamin bahwa undangan digitalmu akan dapat diakses 100% sepanjang waktu,
            meskipun kami berupaya semaksimal mungkin untuk memastikan ketersediaan layanan.
        </li>
    </ul>
</section>

{{-- 8 --}}
<section id="penghentian" data-section>
    <h2>8. Penghentian Layanan</h2>
    <ul>
        <li>
            <strong>Oleh pengguna</strong> — kamu dapat menghapus akunmu kapan saja melalui menu Pengaturan
            di dashboard. Penghapusan akun bersifat permanen dan tidak dapat dibatalkan.
        </li>
        <li>
            <strong>Oleh Theday</strong> — jika Theday memutuskan untuk menghentikan layanan secara keseluruhan,
            kami akan memberikan pemberitahuan minimal 30 hari sebelumnya melalui email terdaftar,
            sehingga kamu punya waktu untuk mengunduh data undanganmu.
        </li>
        <li>
            <strong>Retensi data setelah penghapusan</strong> — setelah akun dihapus, data pribadimu akan
            dihapus dari sistem kami dalam 30 hari, kecuali data yang wajib kami simpan berdasarkan
            ketentuan hukum yang berlaku.
        </li>
    </ul>
</section>

{{-- 9 --}}
<section id="hukum" data-section>
    <h2>9. Hukum yang Berlaku</h2>
    <p>
        Syarat & Ketentuan ini diatur oleh dan ditafsirkan berdasarkan hukum <strong>Republik Indonesia</strong>.
    </p>
    <p>
        Jika terjadi perselisihan antara kamu dan Theday terkait layanan ini, kami mendorong penyelesaian
        melalui <strong>musyawarah mufakat</strong> terlebih dahulu. Jika tidak tercapai kesepakatan,
        perselisihan akan diselesaikan melalui pengadilan yang berwenang di <strong>Jakarta, Indonesia</strong>.
    </p>
</section>

{{-- 10 --}}
<section id="perubahan-syarat" data-section>
    <h2>10. Perubahan Syarat</h2>
    <p>
        Theday berhak memperbarui Syarat & Ketentuan ini dari waktu ke waktu. Jika ada perubahan
        yang bersifat material, kami akan memberitahumu melalui email atau notifikasi di dalam aplikasi
        setidaknya 14 hari sebelum perubahan berlaku.
    </p>
    <p>
        Dengan terus menggunakan Theday setelah perubahan berlaku, kamu dianggap telah menyetujui
        syarat yang telah diperbarui. Jika tidak setuju, kamu dapat menghapus akunmu sebelum
        perubahan berlaku.
    </p>
    <p>
        Tanggal "Terakhir diperbarui" di bagian atas halaman ini mencerminkan versi syarat yang sedang berlaku.
    </p>
</section>

{{-- 11 --}}
<section id="kontak" data-section>
    <h2>11. Kontak</h2>
    <p>
        Jika kamu memiliki pertanyaan tentang Syarat & Ketentuan ini, atau ingin melaporkan pelanggaran,
        hubungi kami di:
    </p>
    <ul>
        <li>Email: <a href="mailto:hello@theday.id">hello@theday.id</a></li>
    </ul>
    <p>Kami akan merespons dalam 3 hari kerja.</p>
</section>

@endsection
