@extends('layouts.legal')

@section('title', 'Kebijakan Cookie')
@section('meta_description', 'Kebijakan Cookie Theday — pelajari cookie apa saja yang kami gunakan dan bagaimana mengatur preferensimu.')
@section('breadcrumb', 'Kebijakan Cookie')
@section('page_title', 'Kebijakan Cookie')
@section('last_updated', '16 April 2026')

@section('toc')
    <a href="#apa-itu-cookie"       class="toc-link">1. Apa itu Cookie?</a>
    <a href="#cookie-kami"          class="toc-link">2. Cookie yang Kami Gunakan</a>
    <a href="#menonaktifkan"        class="toc-link">3. Cara Menonaktifkan Cookie</a>
    <a href="#dampak"               class="toc-link">4. Dampak Menonaktifkan Cookie</a>
    <a href="#kontak"               class="toc-link">5. Kontak</a>
@endsection

@section('content')

{{-- 1 --}}
<section id="apa-itu-cookie" data-section>
    <h2>1. Apa itu Cookie?</h2>
    <p>
        Cookie adalah file teks kecil yang disimpan di perangkatmu (komputer, tablet, atau ponsel) saat
        kamu mengunjungi sebuah website. Cookie membantu website mengingat preferensimu dan memastikan
        layanan berjalan dengan benar.
    </p>
    <p>
        Cookie tidak mengandung virus atau program berbahaya. Cookie hanyalah data teks sederhana yang
        membantu website memberikan pengalaman yang lebih baik dan personal untukmu.
    </p>
    <p>
        Theday menggunakan cookie seminimal mungkin — hanya yang benar-benar diperlukan untuk menjalankan
        layanan dan meningkatkan pengalamanmu.
    </p>
</section>

{{-- 2 --}}
<section id="cookie-kami" data-section>
    <h2>2. Cookie yang Kami Gunakan</h2>

    <h3>Cookie Sesi (Wajib)</h3>
    <p>
        Cookie ini <strong>wajib</strong> untuk menjalankan Theday dan tidak dapat dinonaktifkan.
    </p>
    <ul>
        <li>
            <strong>theday_session</strong> — menyimpan sesi login kamu. Tanpa cookie ini, kamu tidak bisa
            masuk ke akun dan menggunakan fitur Theday. Cookie ini dihapus otomatis saat kamu menutup
            browser (kecuali kamu memilih "Ingat Saya").
        </li>
        <li>
            <strong>XSRF-TOKEN</strong> — melindungi akunmu dari serangan keamanan (Cross-Site Request Forgery).
            Cookie keamanan standar yang digunakan oleh semua aplikasi web modern.
        </li>
    </ul>

    <h3>Cookie Preferensi</h3>
    <p>
        Cookie ini menyimpan pilihan yang kamu buat agar tidak perlu mengaturnya ulang setiap kunjungan.
    </p>
    <ul>
        <li>
            <strong>theday_lang</strong> — menyimpan pilihan bahasamu (Indonesia atau English) di localStorage.
            Ini sebenarnya bukan cookie tradisional, melainkan localStorage yang bekerja serupa.
        </li>
    </ul>

    <h3>Cookie Analitik (Opsional)</h3>
    <p>
        Cookie ini membantu kami memahami cara pengguna menggunakan Theday, sehingga kami dapat
        terus meningkatkan produk. Data analitik bersifat anonim dan tidak digunakan untuk
        mengidentifikasi dirimu secara personal.
    </p>
    <ul>
        <li>
            Saat ini Theday belum menggunakan layanan analitik pihak ketiga seperti Google Analytics.
            Jika kami menambahkan layanan ini di masa mendatang, kebijakan ini akan diperbarui
            dan kamu akan diberitahu.
        </li>
    </ul>
</section>

{{-- 3 --}}
<section id="menonaktifkan" data-section>
    <h2>3. Cara Menonaktifkan Cookie</h2>
    <p>
        Kamu dapat mengatur browser untuk menolak semua cookie atau memberi tahu kamu setiap
        kali cookie dikirimkan. Berikut cara mengatur cookie di browser populer:
    </p>

    <h3>Google Chrome</h3>
    <ul>
        <li>Buka <strong>Pengaturan → Privasi dan keamanan → Cookie dan data situs lain</strong></li>
        <li>Pilih preferensi cookie sesuai keinginanmu</li>
    </ul>

    <h3>Mozilla Firefox</h3>
    <ul>
        <li>Buka <strong>Pengaturan → Privasi & Keamanan → Cookie dan Data Situs</strong></li>
        <li>Centang "Hapus cookie dan data situs saat Firefox ditutup" jika kamu ingin cookie sesi saja</li>
    </ul>

    <h3>Safari (iPhone/iPad/Mac)</h3>
    <ul>
        <li>Pergi ke <strong>Pengaturan → Safari → Privasi & Keamanan</strong></li>
        <li>Aktifkan "Blokir Semua Cookie" sesuai kebutuhanmu</li>
    </ul>

    <h3>Microsoft Edge</h3>
    <ul>
        <li>Buka <strong>Pengaturan → Cookie dan izin situs → Kelola dan hapus cookie dan data situs</strong></li>
    </ul>
</section>

{{-- 4 --}}
<section id="dampak" data-section>
    <h2>4. Dampak Menonaktifkan Cookie</h2>
    <p>
        Menonaktifkan cookie tertentu akan memengaruhi pengalamanmu di Theday:
    </p>
    <ul>
        <li>
            <strong>Cookie sesi dinonaktifkan</strong> — kamu <strong>tidak akan bisa masuk ke akun</strong>
            Theday. Fitur pembuatan undangan, dashboard, dan semua fitur yang memerlukan login tidak akan
            dapat digunakan.
        </li>
        <li>
            <strong>Cookie preferensi dinonaktifkan</strong> — pilihan bahasamu (ID/EN) tidak akan tersimpan,
            sehingga perlu diatur ulang setiap kali kamu membuka Theday. Semua fitur lainnya tetap berfungsi normal.
        </li>
        <li>
            <strong>Cookie analitik dinonaktifkan</strong> — tidak ada dampak pada fungsionalitas Theday.
            Kamu hanya tidak akan membantu kami mengumpulkan data untuk peningkatan produk, yang tentu saja
            sepenuhnya menjadi hakmu.
        </li>
    </ul>
    <p>
        Undangan yang kamu bagikan kepada tamu dapat diakses tanpa login dan tanpa cookie wajib,
        sehingga tamumu tidak perlu khawatir tentang pengaturan ini.
    </p>
</section>

{{-- 5 --}}
<section id="kontak" data-section>
    <h2>5. Kontak</h2>
    <p>
        Jika kamu memiliki pertanyaan tentang penggunaan cookie di Theday, hubungi kami di:
    </p>
    <ul>
        <li>Email: <a href="mailto:hello@theday.id">hello@theday.id</a></li>
    </ul>
</section>

@endsection
