@extends('layouts.legal')

@section('title', 'Kebijakan Privasi')
@section('meta_description', 'Kebijakan Privasi Theday — pelajari bagaimana kami mengumpulkan, menggunakan, dan melindungi data pribadimu.')
@section('breadcrumb', 'Kebijakan Privasi')
@section('page_title', 'Kebijakan Privasi')
@section('last_updated', '16 April 2026')

@section('toc')
    <a href="#pendahuluan"          class="toc-link">1. Pendahuluan</a>
    <a href="#data-dikumpulkan"     class="toc-link">2. Data yang Kami Kumpulkan</a>
    <a href="#penggunaan-data"      class="toc-link">3. Bagaimana Kami Menggunakan Data</a>
    <a href="#berbagi-data"         class="toc-link">4. Berbagi Data dengan Pihak Ketiga</a>
    <a href="#keamanan-data"        class="toc-link">5. Penyimpanan & Keamanan Data</a>
    <a href="#hak-pengguna"         class="toc-link">6. Hak Pengguna</a>
    <a href="#cookie"               class="toc-link">7. Cookie</a>
    <a href="#perubahan-kebijakan"  class="toc-link">8. Perubahan Kebijakan</a>
    <a href="#kontak"               class="toc-link">9. Kontak</a>
@endsection

@section('content')

{{-- 1 --}}
<section id="pendahuluan" data-section>
    <h2>1. Pendahuluan</h2>
    <p>
        Selamat datang di Theday. Kami adalah platform undangan pernikahan digital yang membantu pasangan
        di seluruh Indonesia menciptakan undangan yang elegan, personal, dan mudah dibagikan.
    </p>
    <p>
        Kami sangat peduli dengan privasi kamu. Kebijakan ini menjelaskan secara jujur dan jelas
        data apa yang kami kumpulkan, bagaimana kami menggunakannya, dan bagaimana kami melindunginya.
    </p>
    <p>
        Kebijakan ini berlaku ketika kamu menggunakan layanan Theday — baik saat mendaftar akun,
        membuat undangan, maupun sekadar mengunjungi situs kami di <a href="https://theday.id">theday.id</a>.
    </p>
    <p>
        Dengan menggunakan Theday, kamu menyetujui praktik yang dijelaskan dalam kebijakan ini.
        Jika ada yang kurang jelas, jangan ragu untuk menghubungi kami di <a href="mailto:hello@theday.id">hello@theday.id</a>.
    </p>
</section>

{{-- 2 --}}
<section id="data-dikumpulkan" data-section>
    <h2>2. Data yang Kami Kumpulkan</h2>

    <h3>Data Akun</h3>
    <ul>
        <li>Nama lengkap</li>
        <li>Alamat email</li>
        <li>Password (disimpan dalam bentuk terenkripsi — kami tidak pernah melihat password aslimu)</li>
        <li>Nomor HP WhatsApp (opsional, hanya jika kamu mengisinya)</li>
        <li>Foto profil, jika masuk menggunakan Google</li>
    </ul>

    <h3>Data Undangan</h3>
    <ul>
        <li>Nama mempelai (calon pengantin pria & wanita)</li>
        <li>Tanggal, waktu, dan lokasi acara</li>
        <li>Foto (prewedding, galeri, dll.) yang kamu unggah</li>
        <li>Musik latar yang kamu pilih atau unggah</li>
        <li>Konten teks undangan (cerita cinta, quote, dll.)</li>
        <li>Konfigurasi desain (warna, font, template)</li>
    </ul>

    <h3>Data Tamu</h3>
    <ul>
        <li>Nama tamu yang kamu masukkan ke Guest List</li>
        <li>Nomor WhatsApp tamu (jika kamu mengisinya, untuk fitur kirim pesan)</li>
        <li>Status RSVP tamu yang mengisi konfirmasi kehadiran</li>
        <li>Pesan di buku tamu yang dikirim oleh tamu undangan</li>
    </ul>

    <h3>Data Penggunaan</h3>
    <ul>
        <li>Halaman yang kamu kunjungi di Theday</li>
        <li>Fitur yang kamu gunakan dan frekuensi penggunaannya</li>
        <li>Waktu dan durasi sesi penggunaan</li>
    </ul>

    <h3>Data Teknis</h3>
    <ul>
        <li>Alamat IP</li>
        <li>Jenis browser dan perangkat yang digunakan</li>
        <li>Sistem operasi</li>
    </ul>

    <h3>Data Pembayaran</h3>
    <p>
        Transaksi pembayaran diproses sepenuhnya oleh <strong>Midtrans</strong>, payment gateway terpercaya
        yang telah berlisensi Bank Indonesia. <strong>Theday tidak menyimpan data kartu kredit, nomor kartu,
        atau informasi pembayaran sensitif lainnya</strong> di server kami.
    </p>
</section>

{{-- 3 --}}
<section id="penggunaan-data" data-section>
    <h2>3. Bagaimana Kami Menggunakan Data</h2>
    <p>Data yang kami kumpulkan digunakan untuk:</p>
    <ul>
        <li><strong>Menyediakan layanan</strong> — membuat, mengelola, dan menampilkan undangan digitalmu</li>
        <li><strong>Memproses pembayaran</strong> — mengirimkan data yang diperlukan ke Midtrans untuk transaksi</li>
        <li><strong>Notifikasi layanan</strong> — email konfirmasi akun, info perubahan layanan, pengingat RSVP (bukan email marketing)</li>
        <li><strong>Meningkatkan produk</strong> — memahami fitur yang paling sering digunakan agar kami bisa terus membaik</li>
        <li><strong>Keamanan akun</strong> — mendeteksi aktivitas mencurigakan dan melindungi akunmu</li>
        <li><strong>Dukungan pelanggan</strong> — merespons pertanyaan dan laporan masalah yang kamu kirimkan</li>
    </ul>
    <p>
        Kami <strong>tidak</strong> menggunakan datamu untuk iklan pihak ketiga, dan kami <strong>tidak</strong>
        menjual datamu kepada siapapun.
    </p>
</section>

{{-- 4 --}}
<section id="berbagi-data" data-section>
    <h2>4. Berbagi Data dengan Pihak Ketiga</h2>
    <p>
        Kami hanya membagikan data yang diperlukan kepada penyedia layanan terpercaya yang kami gunakan
        untuk menjalankan Theday. Berikut daftarnya secara transparan:
    </p>
    <ul>
        <li>
            <strong>Midtrans</strong> — memproses pembayaran. Data yang dibagikan: nama, email, dan detail transaksi.
            Kebijakan privasi Midtrans berlaku untuk data yang mereka proses.
        </li>
        <li>
            <strong>DigitalOcean Spaces</strong> — menyimpan file dan foto yang kamu unggah (foto undangan, musik, dll.)
            di server yang aman.
        </li>
        <li>
            <strong>Pusher / Soketi</strong> — mengaktifkan fitur real-time seperti notifikasi RSVP langsung.
            Tidak ada data profil yang dibagikan, hanya sinyal event.
        </li>
        <li>
            <strong>Resend / Postmark</strong> — mengirimkan email transaksional (konfirmasi daftar, reset password, dll.).
            Email provider hanya menerima alamat email tujuan dan konten email.
        </li>
        <li>
            <strong>Google</strong> — jika kamu memilih masuk dengan Google, kami menerima nama, email, dan foto profil
            dari akunmu melalui OAuth. Kami tidak meminta akses ke data Google lainnya.
        </li>
    </ul>
    <p>
        Selain daftar di atas, <strong>Theday tidak membagikan, menjual, menyewakan, atau
        mentransfer data pribadimu kepada pihak ketiga manapun</strong>.
    </p>
</section>

{{-- 5 --}}
<section id="keamanan-data" data-section>
    <h2>5. Penyimpanan dan Keamanan Data</h2>
    <ul>
        <li>Data disimpan di server yang berlokasi di wilayah Asia Tenggara (Singapore/Amsterdam)</li>
        <li>Password disimpan menggunakan algoritma hashing bcrypt — tidak dapat dibaca bahkan oleh tim kami</li>
        <li>Komunikasi antara browsermu dan server Theday dienkripsi menggunakan HTTPS/TLS</li>
        <li>Akses ke database produksi dibatasi hanya untuk tim teknis yang berwenang</li>
        <li>Backup dilakukan secara berkala untuk mencegah kehilangan data</li>
    </ul>

    <h3>Retensi Data</h3>
    <ul>
        <li>Data undangan aktif: disimpan selama akun kamu aktif, dan 30 hari setelah tanggal acara pernikahan</li>
        <li>Data akun: disimpan selama akun kamu aktif</li>
        <li>Setelah kamu menghapus akun, data pribadimu akan dihapus dalam 30 hari, kecuali diwajibkan oleh hukum untuk disimpan lebih lama</li>
    </ul>
</section>

{{-- 6 --}}
<section id="hak-pengguna" data-section>
    <h2>6. Hak Pengguna</h2>
    <p>Kamu memiliki hak penuh atas data pribadimu. Berikut yang bisa kamu lakukan:</p>
    <ul>
        <li><strong>Mengakses data</strong> — kamu dapat meminta salinan data pribadi yang kami simpan tentang kamu</li>
        <li><strong>Memperbarui data</strong> — kamu dapat mengubah nama, email, dan informasi profil kapan saja melalui pengaturan akun</li>
        <li><strong>Menghapus akun</strong> — kamu dapat menghapus akun dan seluruh datamu kapan saja melalui menu Pengaturan Akun</li>
        <li><strong>Mengajukan keberatan</strong> — jika kamu merasa data kamu digunakan tidak sesuai kebijakan ini, kamu dapat mengajukan keberatan kepada kami</li>
        <li><strong>Portabilitas data</strong> — kamu dapat meminta ekspor data dalam format yang dapat dibaca</li>
    </ul>
    <p>
        Untuk menggunakan hak-hak di atas atau mengajukan pertanyaan terkait data pribadimu,
        hubungi kami di <a href="mailto:hello@theday.id">hello@theday.id</a>.
    </p>
</section>

{{-- 7 --}}
<section id="cookie" data-section>
    <h2>7. Cookie</h2>
    <p>
        Theday menggunakan cookie untuk memastikan layanan berjalan dengan baik dan memberikan
        pengalaman yang lebih nyaman untukmu.
    </p>
    <ul>
        <li><strong>Cookie sesi (wajib)</strong> — diperlukan untuk proses login dan menjaga keamanan sesimu. Tanpa cookie ini, kamu tidak bisa masuk ke akun.</li>
        <li><strong>Cookie preferensi</strong> — menyimpan pilihan bahasa (Indonesia/English) agar tidak perlu diatur ulang setiap kunjungan.</li>
        <li><strong>Cookie analitik (opsional)</strong> — membantu kami memahami cara pengguna menggunakan Theday sehingga kami dapat meningkatkan produk.</li>
    </ul>
    <p>
        Kamu dapat menonaktifkan cookie melalui pengaturan browser. Namun perlu diperhatikan bahwa
        menonaktifkan cookie sesi akan membuat kamu tidak bisa masuk ke akunmu.
        Untuk detail lengkap, lihat <a href="{{ route('legal.cookie') }}">Kebijakan Cookie</a> kami.
    </p>
</section>

{{-- 8 --}}
<section id="perubahan-kebijakan" data-section>
    <h2>8. Perubahan Kebijakan</h2>
    <p>
        Kami dapat memperbarui kebijakan privasi ini dari waktu ke waktu. Jika ada perubahan yang
        bersifat material (artinya berdampak signifikan pada cara kami menggunakan datamu),
        kami akan memberitahumu melalui email sebelum perubahan berlaku.
    </p>
    <p>
        Tanggal "Terakhir diperbarui" di bagian atas halaman ini akan selalu mencerminkan versi terkini.
        Dengan terus menggunakan Theday setelah perubahan berlaku, kamu menyetujui kebijakan yang telah diperbarui.
    </p>
</section>

{{-- 9 --}}
<section id="kontak" data-section>
    <h2>9. Kontak</h2>
    <p>
        Jika kamu memiliki pertanyaan, kekhawatiran, atau permintaan terkait privasi dan data pribadimu,
        jangan ragu untuk menghubungi kami:
    </p>
    <ul>
        <li>Email: <a href="mailto:hello@theday.id">hello@theday.id</a></li>
    </ul>
    <p>Kami berkomitmen untuk merespons setiap pertanyaan privasi dalam 3 hari kerja.</p>
</section>

@endsection
