# Budget Planner ↔ Vendor Tracker — Link Design Spec

**Date:** 2026-06-03
**Status:** Draft, ready for review
**Author:** Ardi Sahputra (via JTBD session)

## Summary

Menyatukan dua fitur yang sekarang menyimpan **uang yang sama di dua tempat**: Vendor Tracker (`vendors.total_cost` / `paid_amount`) dan Budget Planner (`wedding_budget_items.planned_amount` / `terpakai`). Pasangan saat ini mencatat harga vendor dua kali — sekali di tab Vendor, sekali di tab Budget — dan kedua angka bisa drift tanpa peringatan. Solusi: **Vendor jadi source of truth harga & pembayaran**, budget item bisa *link* ke vendor dan me-mirror angkanya read-only. Item non-vendor (cincin, mahar, honeymoon) tetap manual.

Niat link sudah ada separuh: `wedding_budget_items` punya kolom `vendor_name` (string bebas). Spec ini menaikkannya jadi FK sungguhan.

## The Job (JTBD)

**Functional job:** "Pesan vendor tanpa jebol budget."
Booking vendor dan tracking budget adalah **satu job**, bukan dua. Vendor adalah line item terbesar dari anggaran (catering, venue). Saat ini produk memecahnya jadi dua tab yang tidak ngobrol.

**Pain yang diselesaikan:**
- *Costliness:* input ganda — catat DP di Vendor, catat lagi di Budget.
- *Common mistake:* `vendors.paid_amount` ≠ budget `dp+final`. Drift diam-diam → pasangan kira aman padahal over.
- *Unresolved:* "Sisa budget realtime setelah booking vendor ini?" tidak terjawab karena dua angka terpisah.

**Emotional job:** "Merasa pegang kendali duit." Dua sumber kebenaran = cemas. Satu angka = kendali.

## Goals

- Hilangkan input ganda: harga & pembayaran vendor cukup diisi sekali (di Vendor).
- Budget summary & category breakdown otomatis ikut angka vendor terbaru.
- Pertahankan kemampuan budget item manual untuk pengeluaran non-vendor.
- Migrasi data lama (`vendor_name` string) tidak hilang / tidak rusak.
- Satu arah sinkron (Vendor → BudgetItem) untuk hindari konflik dua arah.

## Non-Goals

- Sinkron dua arah (edit budget item meng-update vendor). Ditolak — sumber bug.
- Auto-create vendor dari budget item. MVP: vendor dibuat di tab Vendor.
- Split satu vendor ke banyak budget item (DP catering vs pelunasan catering jadi 2 row). MVP enforce **1 vendor = 1 budget item**.
- Multi-currency. Tetap Rupiah integer.
- Auto-matching `vendor_name` lama ke vendor by fuzzy text. MVP: tombol cocokkan manual.

## Decisions

| Topik | Keputusan |
|-------|-----------|
| Source of truth harga+bayar | **Vendor**. Budget item ter-link = mirror read-only |
| Arah sinkron | Satu arah: Vendor → BudgetItem (via accessor, bukan kolom tersimpan) |
| Granularity | 1 vendor ↔ maksimal 1 budget item (enforce unik) |
| Field `vendor_name` lama | Dipertahankan sebagai fallback item non-vendor / belum ke-link |
| Item ter-link, field harga | Read-only di UI; `planned_amount` boleh tetap diisi (target), `terpakai` ikut vendor |
| Hapus vendor | `nullOnDelete` — budget item bertahan, balik jadi manual, `vendor_name` di-snapshot |
| Hapus budget item | Vendor tidak terpengaruh (item cuma view) |
| Data lama | Tombol "Cocokkan ke vendor" per item; tidak auto |

## Architecture

```
┌──────────┐  1      0..1  ┌─────────────────────┐
│ vendors  │◄──────────────│ wedding_budget_items │
└──────────┘   vendor_id   └─────────────────────┘
  (master harga+bayar)        (mirror via accessor)
```

Kunci desain: **agregasi sudah terpusat di accessor `terpakai`**. `BuildBudgetSummaryAction` dan `BuildCategoryBreakdownAction` keduanya memanggil `$item->terpakai`. Ubah satu accessor → seluruh summary ikut benar. Tidak perlu sentuh action.

### Data Model

**Migration: tambah `vendor_id` ke `wedding_budget_items`**

```php
$table->foreignUuid('vendor_id')->nullable()
      ->after('category_id')->constrained()->nullOnDelete();
$table->unique('vendor_id'); // enforce 1 vendor = 1 item (NULL boleh banyak)
```

> Catatan: di MySQL, `UNIQUE` mengizinkan banyak baris `NULL`, jadi item non-vendor tetap bebas. Aman.

**Model `WeddingBudgetItem`:**

```php
protected $fillable = [..., 'vendor_id'];

public function vendor(): BelongsTo
{
    return $this->belongsTo(Vendor::class);
}

public function getTerpakaiAttribute(): int
{
    // Vendor = source of truth saat ter-link
    if ($this->vendor_id !== null && $this->relationLoaded('vendor') && $this->vendor) {
        return (int) $this->vendor->paid_amount;
    }
    if ($this->vendor_id !== null && $this->vendor) {
        return (int) $this->vendor->paid_amount;
    }

    // Item manual: logika dp/final existing
    if ($this->actual_amount !== null) {
        return $this->actual_amount;
    }
    $total = 0;
    if ($this->dp_paid && $this->dp_amount !== null) {
        $total += $this->dp_amount;
    }
    if ($this->final_paid && $this->final_amount !== null) {
        $total += $this->final_amount;
    }
    return $total;
}
```

> **N+1 warning:** breakdown & summary loop banyak item. Pastikan eager-load `->with(['activeItems.vendor'])` di `activeCategories()` dan `activeItems()`. Tanpa ini, tiap item query vendor sendiri.

**`computed_payment_status` saat ter-link** ikut vendor:

```php
if ($this->vendor_id !== null && $this->vendor) {
    $total = (int) ($this->vendor->total_cost ?? 0);
    $paid  = (int) $this->vendor->paid_amount;
    if ($total > 0 && $paid >= $total) return 'paid';
    if ($paid > 0) return 'dp';
    return 'unpaid';
}
// ...fallback existing
```

### Aksi yang TIDAK berubah

- `BuildBudgetSummaryAction` — nol perubahan (pakai `terpakai`).
- `BuildCategoryBreakdownAction` — nol perubahan, kecuali tambah field response (`vendor_id`, `is_linked`) untuk UI badge.

### Frontend (kerjaan utama)

Form Budget Item: ganti input teks `vendor_name` → **dropdown pilih Vendor** (opsi: "— Tanpa vendor (manual) —" + daftar vendor user, kategori sebagai grup).

- Pilih vendor → field `actual/dp/final` jadi read-only, tampil mirror dari vendor + badge "Tersinkron dari Vendor →" (link ke tab vendor).
- Pilih "Tanpa vendor" → field manual seperti sekarang.
- `planned_amount` (target anggaran) tetap editable walau ter-link — ini target pasangan, beda dari harga aktual vendor.

## User POV — Skenario

**Sinta & Bagas, budget Rp150jt.**

1. **Booking pertama.** Bagas tambah vendor "Catering Bu Sri", `total_cost` 45jt, DP 10jt → `paid_amount` 10jt. Buka tab Budget, buat item kategori Catering, dropdown pilih "Catering Bu Sri". Harga langsung muncul: terpakai 10jt, target bisa ia set 40jt. **Tidak ketik angka harga dua kali.**

2. **Bayar pelunasan.** Sebulan kemudian Bagas update vendor `paid_amount` → 45jt. Buka tab Budget — **bar kategori Catering otomatis gerak**, sisa budget total berkurang. Tidak sentuh tab Budget sama sekali.

3. **Item non-vendor.** Sinta beli cincin 8jt tunai. Buat budget item, dropdown "Tanpa vendor", isi manual 8jt. Tetap jalan seperti sekarang.

4. **Lihat kendali.** Dashboard budget: "Terpakai 53jt / 150jt, sisa 97jt." Angka ini benar tanpa rekonsiliasi manual. Inilah emotional job "pegang kendali" terpenuhi.

## Edge Cases

| # | Kasus | Perilaku |
|---|-------|----------|
| 1 | **Vendor dihapus** sementara budget item ter-link | `nullOnDelete` → `vendor_id` jadi NULL, item bertahan. Snapshot nama vendor ke `vendor_name` **sebelum** hapus (model event di Vendor `deleting`), supaya item tidak jadi "tanpa nama". Field harga jadi manual lagi, `terpakai` fallback ke dp/final (mungkin 0 — beri badge "Vendor dihapus, isi manual"). |
| 2 | **`vendor.total_cost` NULL** (vendor dibuat tanpa harga) | `terpakai` = `paid_amount` (0). `computed_payment_status` = 'unpaid'. Budget item tampil terpakai 0, target dari `planned_amount`. Aman. |
| 3 | **`paid_amount` > `planned_amount`** (vendor lebih mahal dari target) | Kategori status `melebihi` muncul otomatis (logika existing `resolveStatus`). Justru fitur, bukan bug. |
| 4 | **Dua budget item coba link vendor sama** | `unique('vendor_id')` tolak di DB. UI: dropdown sembunyikan vendor yang sudah ke-link item lain (query exclude). Beri pesan jika race. |
| 5 | **Item ter-link, user edit `dp_amount` lama** | Field di-disable di UI. Kalaupun lewat API, accessor `terpakai` abaikan dp/final saat `vendor_id` ada — vendor menang. Tidak ada ambiguitas. |
| 6 | **Migrasi `vendor_name` lama** | Kolom dipertahankan. Item lama `vendor_id` NULL, tetap tampil nama teks. Tombol "Cocokkan ke vendor" → dropdown, set `vendor_id`. Tidak auto (nama teks bisa typo / vendor belum ada). |
| 7 | **User belum punya vendor sama sekali** | Dropdown cuma "Tanpa vendor". Opsional: CTA "Tambah vendor dulu →" link ke tab Vendor. |
| 8 | **Vendor pindah kategori** (mis. dari 'lainnya' ke 'catering') | Budget item kategori tidak ikut pindah (kategori budget ≠ kategori vendor, beda taksonomi). Tidak masalah — keduanya independen. Dokumentasikan biar tidak dikira bug. |
| 9 | **Couple account (2 user)** | `vendors.user_id` & budget pakai `EffectiveUser::resolve()`. Pastikan dropdown vendor difilter ke effective user yang sama, bukan `auth()->id()` mentah. Cek konsistensi dengan pola couple-account. |
| 10 | **Soft-delete budget item** ter-link lalu restore | `vendor_id` ikut ter-restore (kolom biasa). Jika vendor sudah dihapus saat item ke-soft-delete, FK `nullOnDelete` sudah set NULL — restore aman, item jadi manual. |
| 11 | **`planned_amount` NULL pada item ter-link** | Boleh. Target opsional. Status kategori pakai planned milik item lain; item ini cuma kontribusi `terpakai`. |
| 12 | **Vendor `paid_amount` di-clamp ke `total_cost`** (logika existing di VendorController) | Mirror ikut nilai ter-clamp. Konsisten, tidak ada surprise. |

## Risks

- **N+1 query** kalau lupa eager-load `vendor`. Mitigasi: tambah `.vendor` di semua `with()` budget, tambah test count query.
- **Kebingungan UX** "kenapa harga tidak bisa kuedit?". Mitigasi: badge eksplisit + link "Edit di Vendor →".
- **Taksonomi ganda** (kategori vendor vs kategori budget) bisa membingungkan jangka panjang. Di luar scope MVP; catat untuk konsolidasi nanti.

## Estimasi

| Bagian | Effort |
|--------|--------|
| Migration `vendor_id` + unique | Kecil |
| Model edit (`vendor()`, `terpakai`, `computed_payment_status`) | Kecil |
| Eager-load + query test | Kecil |
| Vendor `deleting` snapshot nama | Kecil |
| Frontend dropdown + read-only state + badge | Sedang |
| Tombol "Cocokkan ke vendor" (data lama) | Kecil |

Total: ~setengah hari. Tidak ada perubahan pada action agregasi (keuntungan accessor terpusat).

## Definition of Done

- [ ] Budget item bisa link/unlink vendor lewat dropdown.
- [ ] `terpakai` & `computed_payment_status` item ter-link ikut vendor, verified di summary + breakdown.
- [ ] Update `paid_amount` vendor → budget summary berubah tanpa edit budget.
- [ ] Item manual (tanpa vendor) tetap berfungsi penuh seperti sebelumnya.
- [ ] Hapus vendor → item bertahan, nama ter-snapshot, badge "isi manual".
- [ ] Tidak ada N+1 (test query count).
- [ ] Couple account: dropdown vendor terfilter ke effective user.
- [ ] Data `vendor_name` lama tetap tampil + bisa dicocokkan manual.
