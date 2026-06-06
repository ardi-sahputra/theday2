# Checklist Dokumen Nikah — Design Spec

**Tanggal:** 2026-06-04
**Branch:** feat/ai-wedding-planner
**Status:** Disetujui untuk implementasi

---

## 1. Ringkasan

Fitur baru di dalam **Wedding Planner**: tab kedua **Dokumen** yang menemani pasangan
mengurus surat-surat administrasi pernikahan (job: *lacak + simpan file + panduan*).

Bukan sekadar daftar centang — tiap dokumen punya: status, slot upload file (privat),
dan panduan ringkas (di mana urus, syarat, urutan, estimasi waktu relatif `event_date`).

Daftar dokumen **terverifikasi dari sumber 2025** (lihat §12 Sumber). Konten katalog
disimpan sebagai seed/config statis agar mudah diperbarui saat aturan berubah.

## 2. Jobs-to-be-Done

- **Functional:** tahu surat apa wajib disiapkan, lacak progres, simpan scan, urus tepat urutan & tenggat.
- **Social:** tampil siap/teratur di mata keluarga & petugas KUA/Disdukcapil.
- **Emotional:** tenang — tidak takut ada berkas kurang menjelang hari H.

**Pain yang diatasi:** info syarat tersebar & sering usang; bingung urutan; takut salah/kurang;
beda domisili bikin makin rumit.

## 3. Penempatan & UX (Opsi 1 — tab dalam Wedding Planner)

- Halaman `Dashboard/Checklist/Index` mendapat **segmented switch** atas: `Tugas | Dokumen`.
  Pola visual mengikuti `ChecklistViewToggle.vue` yang sudah ada.
- State lewat query `?tab=dokumen` (deep-link & tombol back konsisten). Default `tugas`.
- **Mobile:** tab "Planner" menampilkan segmented yang sama; layout list vertikal.
- **Entry kedua:** kartu "Dokumen Nikah" di Dashboard, tampil saat fase persiapan aktif,
  menautkan ke `?tab=dokumen`.

**Alasan (musiman tapi penting):** tab kedua = terlihat saat dibutuhkan, diam saat tidak,
nol biaya slot nav permanen. Reuse pola toggle → murah.

## 4. Anatomi tab Dokumen

- **Progress hero:** "X/Y dokumen beres" + ring/bar.
- **Pemilih konteks (sekali, bisa diubah):**
  - **Jalur:** `Islam / KUA` ↔ `Sipil / Disdukcapil` (preset — *bukan* menyimpan agama, lihat §6).
  - **Beda domisili?** toggle → menyuntik baris Surat Rekomendasi/Numpang Nikah.
  - **Kondisi personal** (chip multi): `<21 th`, `<19 th`, `duda/janda`, `TNI/Polri/PNS`,
    `daftar <10 hari` → menampilkan baris kondisional yang relevan saja.
- **Kartu dokumen (per baris):**
  - Judul + chip status: `Belum` / `Proses` / `Beres`.
  - Slot **upload** (scan/foto): preview, ganti, hapus. File privat.
  - **Panduan** (expand): di mana urus, syarat ringkas, urutan, estimasi waktu / tenggat
    relatif `event_date`.
  - Badge `Wajib` / `Jika …` (kondisional).

## 5. Katalog dokumen (verified 2025)

> Konten katalog = **seed/config statis** (`config/wedding_documents.php` atau seeder).
> Tiap entri: `key, label, jalur[], conditions[], guidance{where, requirements, order, lead_time}`.

### Universal (dua jalur)
- KTP elektronik (tiap pihak)
- Kartu Keluarga (KK)
- Akta Kelahiran
- Pas foto **2×3 & 4×6 latar biru** (mempelai pria kanan, wanita kiri)
- Surat pengantar RT/RW

### Jalur Islam / KUA
- **N1** — Surat Pengantar/Keterangan untuk Nikah (dari kelurahan)
- **N2** — Surat Keterangan Asal-Usul
- **N3** — Surat Persetujuan Mempelai
- **N4** — Surat Keterangan Tentang Orang Tua
- **Bimwin / Suscatin** — Sertifikat Bimbingan Perkawinan
- **Sertifikat Layak Kawin** (skrining kesehatan)

Kondisional:
- **N5** — Surat Izin Orang Tua — *jika usia < 21 tahun*
- **Dispensasi Pengadilan** — *jika usia < 19 tahun*
- **Akta Cerai / Akta Kematian** pasangan terdahulu — *jika duda/janda*
- **Surat Izin Atasan** — *jika TNI/Polri/PNS*
- **Dispensasi Camat** — *jika mendaftar < 10 hari kerja sebelum akad*

### Jalur Sipil / Disdukcapil (non-muslim)
- Surat pemberkatan agama (gereja/lembaga) — **dilegalisir**
- Surat keterangan belum menikah
- KTP **2 saksi** (usia > 21)
- KTP orang tua / wali
- Surat pengantar lurah (asli)
- Pas foto berdampingan 4×6

Kondisional:
- Surat baptis / keterangan agama — *jika ada / diminta*
- Surat izin atasan — *jika TNI/Polri*

Catatan jalur sipil: pendaftaran **maks 60 hari** setelah pemberkatan.

### Beda domisili (add-on, jalur KUA)
- **Surat Rekomendasi / Numpang Nikah** dari KUA asal → KUA lokasi akad
- Tenggat: daftar di KUA lokasi **≤ 10 hari** sebelum akad

## 6. Data & penyimpanan

### Katalog (statis)
`config/wedding_documents.php` (atau seeder) — sumber tunggal label + panduan + jalur + flag kondisi.
Mengubah aturan = edit satu file. Tidak ada konten panduan di DB.

### Tabel `wedding_documents` (per pasangan)
Kolom: `id`, `wedding_plan_id`, `key`, `status` (enum belum/proses/beres),
`file_path` (nullable), `note` (nullable), `completed_at` (nullable), timestamps.
Baris dibuat lazily saat pertama disentuh, atau di-seed dari katalog saat tab dibuka.

### Konteks di `WeddingPlan` (kolom baru)
- `document_path` enum: `kua` | `sipil` (nullable sampai dipilih).
- `document_flags` JSON: `{ beda_domisili: bool, under21: bool, under19: bool,
  widowed: bool, tni_polri: bool, late_register: bool }`.

**Tidak ada kolom/tabel agama** — sejalan dengan keputusan field agama yang ditunda
(privasi). `document_path` adalah pilihan preset, bukan atribut identitas.

### File
- Disimpan di disk **privat** (`storage/app/private/wedding-documents/...`).
- Akses lewat **signed route** + otorisasi pemilik plan. Tidak pernah public
  (KTP/KK/akta = data sensitif).
- Validasi: jenis (jpg/png/pdf), ukuran maks (mis. 5 MB).

## 7. Backend

Route group baru di bawah prefix dashboard (sejajar `checklist.*`):

```
GET    /documents/data                 documents.data      # katalog terfilter + status pasangan
PATCH  /documents/context              documents.context   # set jalur + flags
PATCH  /documents/{key}/status         documents.status    # belum/proses/beres
POST   /documents/{key}/file           documents.file.store
DELETE /documents/{key}/file           documents.file.destroy
GET    /documents/{key}/file           documents.file.show # signed, privat
```

- **Controller:** `DocumentController` + **`DocumentService`** tipis (resolve katalog +
  merge status, filter by jalur/flags). Reuse pola `resolveOrCreatePlan` dari `ChecklistController`.
- Filter katalog server-side berdasar `document_path` + `document_flags` sebelum kirim ke klien.

## 8. Frontend (Vue/Inertia)

- `Index.vue` Wedding Planner: tambah `activeTab` (`tugas`/`dokumen`) dari `?tab`.
  Render `DocumentsTab.vue` saat `dokumen`. Tab `tugas` = perilaku existing, tak berubah.
- Komponen baru:
  - `DocumentsTab.vue` — kontainer: hero + context picker + list.
  - `DocumentContextPicker.vue` — jalur toggle + flags chips.
  - `DocumentCard.vue` — status + upload + panduan expand.
  - `documentsCache.js` (opsional) — pola sama `checklistCache` untuk revisit instan.
- i18n: key di bawah `dashboard.documents.*`.
- Tablet band (~480–1023px) wajib ditangani — hindari gap phone-frame-card di iPad portrait.

## 9. Error handling

- Upload gagal (jenis/ukuran/jaringan) → pesan inline di kartu, tidak hilangkan progres lain.
- Belum pilih jalur → tampil empty-state ramah ("Pilih jalur dulu") sebelum list.
- File hilang/terhapus di disk → status tetap, tampilkan "file tidak ditemukan, upload ulang".

## 10. Testing

- **Unit/Feature (Pest):** filter katalog per jalur+flags; CRUD status; upload/owner-authz;
  signed-route menolak non-owner; validasi jenis/ukuran file.
- **Frontend:** tab switch via query; context picker menyuntik/menyembunyikan baris;
  upload → preview → hapus.
- Anti-halu: test snapshot katalog mencocokkan daftar §5 (cegah regresi konten).

## 11. Out of scope (YAGNI)

- OCR / auto-isi data dari scan.
- Integrasi langsung ke sistem KUA/Disdukcapil.
- Reminder push khusus dokumen (bisa menyusul; reuse infra reminder checklist).
- Multi-bahasa konten panduan selain ID (sekarang ID dulu).

## 12. Sumber (verifikasi 2025)

- kekondangan.id — Syarat Nikah Terbaru 2025 (KUA & Sipil)
- webnikah.com — Berkas Pernikahan di KUA Lengkap (Update 2025)
- Bridestory — Syarat Nikah Beda Kota 2025
- Disdukcapil Tangerang — Syarat Penerbitan Akta Perkawinan (Non-Muslim)
- Mega Syariah — Surat Numpang Nikah: Syarat & Langkah
