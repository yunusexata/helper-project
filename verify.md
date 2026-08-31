# Verifikasi Sistem: Helper Daily Jobdesk & Monitoring History

Dokumen ini memuat hasil verifikasi menyeluruh terhadap alur kerja input pekerjaan harian (rutinitas dan request ad-hoc), pencatatan catatan (note), lampiran bukti foto (attachment), serta visualisasi pada dasbor monitoring dan riwayat.

---

## 1. Matrix Alur Data (Data Flow Lifecycle)

### A. Pekerjaan Rutinitas (Routines)
| Tahapan | Komponen / Method | Data Disimpan | Target Tabel | Status di Datatable |
| :--- | :--- | :--- | :--- | :--- |
| **Mulai Kerja** | `Dashboard::startTask($firstActivityId)` | `subject_id = firstActivityId`<br>`subject_type = HelperJobdeskRoutine`<br>`start_at = now()`<br>`finish_at = null` | `helper_jobdesk_daily_histories` | **Sedang Berjalan** (Kuning/Amber) |
| **Input Catatan & Foto** | Form modal / panel detail | `note`, `attachments[]` (max 5MB/gambar) | Livewire state `$this->note`, `$this->attachments` | - |
| **Selesai Kerja** | `Dashboard::completeTask($firstActivityId)` | `finish_at = now()`<br>`note = $this->note`<br>Upload foto ke disk `public` | `helper_jobdesk_daily_histories`<br>`helper_jobdesk_daily_history_attachments` | **Selesai** (Hijau / Emerald) |

### B. Pekerjaan Request Ad-hoc
| Tahapan | Komponen / Method | Data Disimpan | Target Tabel | Status di Datatable |
| :--- | :--- | :--- | :--- | :--- |
| **Mulai Request** | `Dashboard::startRequest()` | `activity_name`, `day`, `employee_whitelists_id`<br>`subject_type = HelperJobdeskRequest`<br>`start_at = now()`, `finish_at = null` | `helper_jobdesk_requests`<br>`helper_jobdesk_daily_histories` | **Sedang Berjalan** (Kuning/Amber) |
| **Selesai Request** | `Dashboard::completeRequest()` | `finish_at = now()`<br>`note = $this->requestNote`<br>Upload foto ke disk `public` | `helper_jobdesk_daily_histories`<br>`helper_jobdesk_daily_history_attachments` | **Selesai** (Hijau / Emerald) |
| **Batal Request** | `Dashboard::cancelRequest()` | SweetAlert2 confirm -> Soft delete history & request | Data dihapus dari tampilan | Terhapus dari datatable |

---

## 2. Pengujian & Verifikasi Potensi Bug / Mismatch Data

### Temuan 1: State Bleed pada Penyelesaian Tugas Rutin (FIXED)
- **Gejala Potensial**: Setelah Helper menyelesaikan satu kelompok tugas rutin dengan catatan dan foto, catatan dan foto tersebut tidak di-reset jika kelompok berikutnya langsung dibuka tanpa perpindahan halaman.
- **Dampak**: Catatan atau foto dari tugas sebelumnya dapat terbawa ke laporan kelompok tugas berikutnya.
- **Penyelesaian**: Menambahkan reset eksplisit pada `Dashboard::completeTask()`:
  ```php
  $this->note = '';
  $this->attachments = [];
  $this->resetErrorBag();
  ```

### Temuan 2: Konsistensi Nama Kolom Relasi Attachment (VERIFIED OK)
- **Pemeriksaan**: Skema migrasi `helper_jobdesk_daily_history_attachments` menggunakan nama kolom non-standar: `helper_jobdesk_daily_histories` (bukan `helper_jobdesk_daily_history_id`).
- **Verifikasi**:
  - Model `HelperJobdeskDailyHistory::attachments()` secara eksplisit memetakan `foreignKey: 'helper_jobdesk_daily_histories'`.
  - `Datatable.php` query attachment menggunakan `whereIn('helper_jobdesk_daily_histories', $historyIds)`.
  - Insert pada `Dashboard.php` (baik routine maupun request) menggunakan key `'helper_jobdesk_daily_histories' => $history->id`.
  - **Status**: Sinkron 100% di semua level kode.

### Temuan 3: Konversi Zona Waktu & Perhitungan Durasi (VERIFIED OK)
- **Pemeriksaan**: Helper bekerja di zona waktu Jakarta (WIB / UTC+7). Jika timestamp disimpan dalam UTC pada database, apakah waktu mulai, selesai, dan durasi sesuai?
- **Verifikasi**:
  - `Datatable.php` dan `helper-dashboard.blade.php` secara eksplisit menjalankan:
    `Carbon::parse($item->start_at)->setTimezone('Asia/Jakarta')`
    `Carbon::parse($item->finish_at)->setTimezone('Asia/Jakarta')`
  - Durasi dihitung via `$diffMinutes = $start->diffInMinutes($finish)`. Jika durasi >= 60 menit, diformat `X jam Y menit`; jika < 60 menit diformat `Y menit`.
  - Tugas yang belum selesai (`finish_at == null`) menghasilkan `-`.
  - **Status**: Akurat dan konsisten dalam WIB.

### Temuan 4: Relasi User dengan Employee Whitelist (VERIFIED OK)
- **Pemeriksaan**: Data monitoring di-query berdasarkan `employee_whitelists_id`.
- **Verifikasi**:
  - Helper 1 terhubung via `musa@exata-indonesia.id` ke `employee_whitelists.id = 1`.
  - Jika seorang user ber-role Helper belum terdaftar pada `employee_whitelists`, sistem menangani dengan fallback aman (menghasilkan empty state tanpa error SQL).
  - **Status**: Terverifikasi aman.

### Temuan 5: Grouping Agregasi PostgreSQL vs SQLite (VERIFIED OK)
- **Pemeriksaan**: Fungsi agregasi string berbeda antara driver database (`STRING_AGG` pada PostgreSQL dan `GROUP_CONCAT` pada SQLite).
- **Verifikasi**:
  - `Datatable.php` mendeteksi driver via `DB::getDriverName() === 'pgsql' ? "STRING_AGG(r.activity_name, '|||' ORDER BY r.order)" : "GROUP_CONCAT(r.activity_name, '|||')"`.
  - Sub-aktivitas dipisah dengan delimiter `|||` dan dirender sebagai checklist bersarang yang rapi pada kolom **Nama Aktivitas**.
  - **Status**: Kompatibel dengan semua environment.

### Temuan 6: Symlink Storage & Akses Publik Bukti Foto (VERIFIED OK)
- **Pemeriksaan**: Foto bukti diunggah ke path `daily-history-attachments/` pada disk `public`.
- **Verifikasi**:
  - Symlink `public/storage` telah terhubung ke `storage/app/public`.
  - Thumbnail foto di render dengan tag `<img>` dan link lightbox `target="_blank"` yang mengarah ke `asset('storage/' . $att->path)`.
  - Gambar dapat diakses langsung oleh browser tanpa error 404 / 403.
  - **Status**: Terverifikasi bekerja optimal.

---

## 3. Checklist Validasi Fungsional

- [x] Input Mulai & Selesai Rutinitas Helper berhasil tercatat di database.
- [x] Input Catatan (Note) pada Rutinitas tersimpan dan muncul di kolom Catatan Laporan datatable.
- [x] Upload Bukti Foto pada Rutinitas tersimpan di storage dan thumbnail muncul di datatable.
- [x] Input Mulai & Selesai Request Ad-hoc berhasil tercatat di database.
- [x] Input Catatan & Foto pada Request Ad-hoc tersimpan dan muncul di datatable.
- [x] Batalkan Request berfungsi dengan konfirmasi SweetAlert2 dan menghapus data aktif.
- [x] Filter Petugas & Tanggal pada dasbor publik bekerja reaktif secara real-time.
- [x] Seluruh alert browser default (`wire:confirm`, `alert()`) telah digantikan oleh SweetAlert2.
- [x] Route publik `/` langsung menampilkan monitoring datatable tanpa tombol toggle / tutup.

---
*Laporan verifikasi dibuat: 31 Agustus 2026*
*Sistem: PT Sumber Rezeki Exata Indonesia - Helper Management System*
