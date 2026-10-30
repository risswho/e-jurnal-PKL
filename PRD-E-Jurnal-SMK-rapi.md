**# PRD — E-Jurnal SMK**

****Product Requirements Document — Sistem Pencatatan Jurnal Harian PKL & Bimbingan Siswa SMK****

- Versi dokumen: 1.4

- Tanggal: 15 September 2026

- Status: Implemented (production-ready di XAMPP lokal)

- Riwayat: v1.4 — adaptasi UI Medical Dashboard (palette teal, design system CSS variables, hero banner, stat cards real-time, paginasi client-side) + 12 halaman agregasi Kelas/Absensi/Laporan/Profil + skema fresh `database/database.sql` (DB `jurnal`) + seed akun dev; v1.3 — segarkan backup (dump + arsip foto skema pembimbing) + rapikan PRD; v1.2 — kembalikan relasi guru pembimbing ala walikelas (siswa.id_guru, diatur admin, filter strict di akun guru); v1.1 — tambah branding logo foto + filter siswa di cetak laporan; v1.0 — rilis awal.

---

**## 1. Ringkasan Produk**

E-Jurnal SMK adalah aplikasi web untuk pencatatan ****jurnal harian PKL**** oleh siswa, ****verifikasi jurnal**** dan ****dokumentasi bimbingan**** oleh guru pembimbing, serta ****pengelolaan data**** oleh admin — menggantikan pencatatan manual (buku/logbook kertas) yang sulit dimonitor dan direkap.

- ****Teknologi:**** PHP native 8.2 + MySQL (mysqli, prepared statement), Tailwind CSS via CDN, JavaScript vanilla. Tanpa dependensi composer — bisa jalan langsung di XAMPP.

- ****Design system:**** CSS variables di `assets/css/style.css` (`--primary:#0EA5A0` teal, `--primary-dark:#0B7C6C`, `--radius:16px`, token light+dark) + class utilitas (`.card`, `.stat-card`, `.nav-link`, `.btn-\*`, `.form-input`, `.table-medical`, `.hero-medical`, `.pg-btn`); font Poppins (heading/angka) + Inter (body). Gaya visual terinspirasi medical dashboard modern: clean, banyak white space, card rounded, sidebar berseksi.

- ****Database:**** `jurnal` — 5 tabel: `admin`, `siswa`, `guru`, `jurnal`, `bimbingan`. Instalasi fresh memakai `database/database.sql` (skema + seed akun dev). Arsip dump lama tetap ada di `backup/` (nama DB lama `e_jurnal_smk`).

- ****Role:**** 3 peran — `admin`, `guru`, `siswa` — masing-masing dengan area dan menu sendiri.

- ****UX:**** responsif (mobile + desktop), dark mode, hero banner "Selamat Datang, [Nama]", stat cards real-time (termasuk Jurnal Hari Ini + `+N bulan ini`), logo foto sekolah (sidebar, login, favicon), toast notifikasi, modal konfirmasi hapus, lightbox foto, pencarian + filter + paginasi client-side di tabel.

Entry point aplikasi adalah `index.php` yang mengarahkan ke `auth/login.php`.

---

**## 2. Tujuan & Latar Belakang**

1\. Siswa mencatat kegiatan magang harian secara terstruktur beserta foto bukti, kapan saja.

2\. Guru memverifikasi jurnal (Setujui/Revisi) dan mencatat dokumentasi bimbingan — hanya untuk siswa bimbingannya.

3\. Admin mengelola akun & data induk (siswa, guru, admin) termasuk import massal via CSV dan koreksi data saat user lupa kredensial.

4\. Laporan jurnal dapat dicetak (print-friendly) oleh guru, dengan filter pilih siswa.

5\. Seluruh peran mendapat rekap agregat siap baca (Kelas, Absensi, Laporan, Profil) tanpa tabel database baru.

---

**## 3. Pengguna & Hak Akses**

| Modul | Admin | Guru | Siswa |

|---|---|---|---|

| Login (NISN / NIP / username + password) | Ya | Ya | Ya |

| Dashboard + statistik | Ya (global) | Ya (bimbingan sendiri) | Ya (milik sendiri) |

| Kelola data siswa (tambah/edit/hapus) | Ya | — | — |

| Kelola data guru (tambah/edit/hapus) | Ya | — | — |

| Kelola data admin | Ya | — | — |

| Import CSV (siswa, guru, admin) | Ya | — | — |

| Isi / edit / hapus jurnal + foto | — | — | Ya (milik sendiri) |

| Verifikasi jurnal (Setujui/Revisi) | — | Ya (hanya jurnal siswa bimbingannya, strict termasuk via URL) | — |

| Tambah dokumentasi bimbingan + foto | — | Ya (hanya untuk siswa bimbingannya) | — |

| Lihat semua jurnal / arsip bimbingan | Ya | — | — |

| Cetak laporan jurnal | — | Ya (hanya siswa bimbingannya) | — |

| Rekap Kelas (agregasi, read-only) | Ya (global) | Ya (bimbingan sendiri) | Ya (kelas sendiri + teman sekelas) |

| Rekap Absensi (agregasi, read-only) | Ya (semua siswa) | Ya (bimbingan sendiri) | Ya (milik sendiri, per bulan) |

| Laporan (agregasi, read-only + cetak) | Ya (pintu masuk data) | Ya (pintu masuk cetak) | Ya (milik sendiri + cetak) |

| Profil (read-only) | Ya (akun sendiri) | Ya (akun sendiri) | Ya (profil PKL sendiri) |

****Relasi pembimbing:**** setiap siswa punya ****1 guru pembimbing**** (`siswa.id_guru → guru.id_guru`, seperti walikelas); 1 guru bisa membimbing banyak siswa. Assignment ****hanya oleh admin**** (Tambah/Edit Siswa atau Import CSV via `nip_pembimbing`); siswa hanya melihat nama pembimbingnya (read-only). Siswa tanpa pembimbing (`NULL`) tidak muncul di akun guru manapun.

****Kredensial login:**** siswa memakai ****NISN****, guru memakai ****NIP****, admin memakai ****username****. Password tersimpan ter-hash (`password_hash`, kolom `password` untuk siswa/guru dan `kode_unik` untuk admin) dan tidak pernah ditampilkan.

****Akun seed dev**** (dari `database/database.sql` — khusus pengembangan, wajib diganti/dihapus di produksi):

| Role | Login | Password |

|---|---|---|

| Admin | `admin` | `admin123` |

| Guru | NIP `1001` (Budi Santoso), `1002` (Siti Aminah) | `guru123` |

| Siswa | NISN `001`, `002`, `003` | `siswa123` |

---

**## 4. Kebutuhan Fungsional**

**### 4.1 Autentikasi (`auth/`)**

- `auth/login.php` — layout split: hero gradient (branding + 3 kartu peran) + form card; pilih role → label/placeholder username menyesuaikan (NISN/NIP/Username); toggle tampil password; toggle tema. Redirect otomatis jika session aktif. Fungsi auth tidak berubah.

- `auth/proses_login.php` — verifikasi per role dengan prepared statement + `password_verify`; session menyimpan `login`, `role`, id, dan nama.

- `auth/logout.php` — keluar dan kembali ke login.

- Proteksi halaman via `require_login('\<role>')` (`includes/helpers.php`); akses lintas role ditolak ke halaman login.

- Halaman error konsisten via `abort()` (`includes/error.php`); notifikasi via `?success= / ?deleted= / ?verified= / ?error=` dirender `includes/toast.php` (auto-dismiss).

**### 4.2 Admin**

****Dashboard**** (`admin/dashboard.php`)

- Hero "Selamat Datang, [Nama]" + subtitle + aksi cepat (Tambah Siswa, Tambah Guru, Import Data, lihat semua jurnal).

- 4 stat cards real-time: Total Jurnal (`+N bulan ini`), Jurnal Hari Ini (`WHERE tanggal_kegiatan = CURDATE()`), Total Siswa (`N kelas terdaftar` via `COUNT(DISTINCT kelas)`), Total Guru (subtext jumlah bimbingan) + daftar 5 jurnal terbaru dengan badge status.

****Kelola Siswa**** (`admin/siswa.php`, `tambah_siswa.php`, `proses_tambah_siswa.php`, `edit_siswa.php`, `proses_edit_siswa.php`, `hapus_siswa.php`)

- Tabel: NISN (mono), nama + jurusan, kelas, DUDI, ****kolom Pembimbing (nama+NIP, badge "Belum ada" bila NULL)****, kolom Aksi (Edit + Hapus).

- Tambah: NISN (unik), nama, kelas, jurusan, DUDI, ****guru pembimbing (dropdown wajib, divalidasi EXISTS)****, password.

- ****Edit (semua field termasuk password & pembimbing):**** NISN dapat dikoreksi bila salah (cek duplikat mengecualikan diri sendiri); password bersifat opsional — kosong = tidak diubah, diisi = diganti (di-hash). Solusi untuk kasus ****lupa password / lupa NISN****.

- Hapus: konfirmasi modal; jurnal & foto bimbingan terkait ikut dihapus dari `uploads/`.

****Kelola Guru**** (`admin/guru.php`, `tambah_guru.php`, `proses_tambah_guru.php`, `edit_guru.php`, `proses_edit_guru.php`, `hapus_guru.php`)

- Tabel: NIP, nama, kolom Aksi (Edit + Hapus).

- Tambah/edit: NIP (unik, dapat dikoreksi), nama, password (saat edit: opsional, kosong = tidak diubah). Solusi untuk kasus ****lupa password / lupa NIP****.

- Hapus: catatan bimbingan guru tersebut ikut terhapus (CASCADE); data siswa tidak terhapus, tetapi yang dibimbingnya menjadi tanpa pembimbing (`id_guru` → `NULL` via `SET NULL`) sampai di-assign ulang.

****Kelola Admin**** (`admin/admin.php`)

- Daftar admin + tambah manual (username unik, password di-hash ke `kode_unik`) + hapus (tidak bisa hapus akun sendiri; minimal 1 admin tersisa) + tombol Template/Import CSV.

****Semua Jurnal**** (`admin/jurnal.php`)

- Monitoring seluruh jurnal: No, tanggal, siswa (nama+NISN+kelas), materi/kegiatan + DUDI, guru/pembimbing lapangan, badge status, foto (lightbox).

- Pencarian teks + dropdown ****filter nama siswa**** + dropdown filter status (ketiganya kombinasi AND, client-side) + ****paginasi client-side 10 baris/halaman****.

****Dokumentasi Bimbingan**** (`admin/bimbingan.php`)

- Arsip: tanggal, guru (nama+NIP), siswa (nama+NISN+kelas), DUDI, keterangan, foto.

- Pencarian teks + dropdown ****filter nama guru**** (kombinasi AND, client-side).

****Halaman agregasi**** (`admin/kelas.php`, `admin/absensi.php`, `admin/laporan.php`, `admin/profil.php`) — lihat §4.6.

**### 4.3 Import Data CSV (admin)**

File: `admin/import.php`, `admin/proses_import.php`, `admin/download_template.php`, `includes/import_helpers.php`. Tipe yang didukung: ****siswa, guru, admin**** (import jurnal & bimbingan tidak disediakan).

- ****Halaman wizard 3 langkah**** (`import.php?tipe=siswa|guru|admin`): kartu pemilih tipe (ikon + jumlah kolom) → Langkah 1 unduh template (chip header wajib/opsional + tabel 2 baris contoh) → Langkah 2 panduan isi di Excel → Langkah 3 zona drag-drop upload + tombol Import. Kolom kanan sticky berisi ringkasan tipe + tombol ke halaman kelola terkait.

- ****Template**** (`download_template.php?tipe=...`): file CSV UTF-8 (dengan BOM agar rapi dibuka Excel) berisi header + 2 baris contoh.

- ****Proses**** (`proses_import.php`): validasi `.csv`, maks 2 MB, maks 2000 baris; deteksi delimiter (`, ;` tab `|`) otomatis; header dinormalisasi; duplikat (NISN/NIP/username) ****dilewati bukan ditimpa****; password otomatis di-hash; ****kolom `nip_pembimbing` (siswa) wajib dan di-resolve ke `guru.id_guru` — NIP tak dikenal membuat baris dilewati dengan pesan error****.

- ****Laporan hasil:**** Total / Berhasil / Dilewati + progress bar + tabel rincian error per nomor baris (maks 50, tersimpan di session ≤ 5 menit). Tipe tak dikenal (termasuk URL lama `jurnal`/`bimbingan`) ditolak dengan pesan yang jelas.

Format kolom:

| Tipe | Header CSV |

|---|---|

| siswa | `nisn, nama_lengkap, kelas, jurusan, dudi_magang, nip_pembimbing, password` |

| guru | `nip, nama_guru, password` |

| admin | `username, password` |

**### 4.4 Guru (strict: hanya siswa bimbingan sendiri)**

- ****Dashboard**** (`guru/dashboard.php`): hero "Selamat Datang, [Nama]" + 3 stat cards (Total Jurnal Bimbingan, Jurnal Hari Ini, Total Siswa Bimbingan + subtext bimbingan), 5 jurnal terbaru bimbingan dengan badge status, menu cepat.

- ****Jurnal Siswa**** (`guru/jurnal_semua.php`): ****hanya jurnal siswa bimbingannya**** (`WHERE s.id_guru=?`) + kolom No + pencarian teks + ****filter nama siswa (bimbingan saja)**** + filter status + paginasi 10/halaman + tombol Setujui/Revisi per baris + link "Lihat Semua" per siswa.

- ****Jurnal per siswa**** (`guru/jurnal_siswa.php`): akses dibatasi `WHERE id_siswa=? AND id_guru=?` — bukan bimbingan → 403.

- ****Verifikasi**** (`guru/verifikasi.php`): cek kepemilikan via `JOIN siswa ... AND s.id_guru=?` sebelum update; aksi `setuju → Disetujui`, `revisi/tolak → Ditolak` (cek keberadaan jurnal; redirect balik ke asal via `from=semua|siswa` + toast `?verified=`).

- ****Aktivasi status**** (`guru/setup_status.php`): one-click `ALTER TABLE jurnal ADD COLUMN status ENUM('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu'` bila kolom belum ada — ****hanya untuk instalasi lama****; skema fresh `database.sql` sudah memuat kolom ini (halaman otomatis menawarkan bila fitur verifikasi belum aktif).

- ****Bimbingan**** (`guru/bimbingan.php`, `tambah_bimbingan.php`, `proses_bimbingan.php`): dropdown ****hanya siswa bimbingan**** + validasi server `AND id_guru=?` (tanggal default hari ini, nama DUDI terisi otomatis dari data siswa, foto opsional JPG/PNG ≤ 2 MB dengan preview); tabel riwayat milik sendiri + pencarian teks + ****filter nama guru****. Form memakai class `.form-input`/`.form-label`/`.btn-\*` (nama field POST tidak berubah).

- ****Riwayat Bimbingan**** (`guru/riwayat_bimbingan.php`): tabel milik sendiri + pencarian teks + ****filter nama guru****.

- ****Cetak Laporan**** (`guru/cetak_laporan.php`): laporan ****hanya siswa bimbingan**** (dropdown + baris difilter `WHERE id_guru=?`; `?siswa=` milik guru lain → pesan "bukan bimbingan"), CSS print yang menyembunyikan sidebar/header/tombol, tombol Cetak; ****filter pilih siswa**** via GET (dropdown + Tampilkan/Reset, kop menyebut siswa terfilter, id tak dikenal → pesan).

- ****Halaman agregasi**** (`guru/kelas.php`, `guru/absensi.php`, `guru/laporan.php`, `guru/profil.php`) — lihat §4.6.

**### 4.5 Siswa**

- ****Dashboard + Riwayat**** (`siswa/dashboard.php`): hero "Selamat Datang, [Nama]" + kartu profil PKL (nama, kelas, jurusan, DUDI, ****guru pembimbing read-only****), 4 kartu (Total + `+N bulan ini`, Jurnal Hari Ini, Menunggu, Disetujui), tabel riwayat (kolom No, Tanggal, Materi/Kegiatan, Status, Foto, Aksi) + pencarian teks + paginasi 8/halaman + aksi Detail/Edit/Hapus per jurnal.

- ****Isi Jurnal**** (`siswa/tambah_jurnal.php`, `siswa/proses_tambah.php`): tanggal (default hari ini), nama DUDI (terisi dari profil), pembimbing (nama pembimbing lapangan/di tempat magang — teks bebas, beda dari guru pembimbing sekolah), kegiatan (wajib), keterangan (opsional), foto JPG/PNG ≤ 2 MB (opsional, preview, nama file unik). Form memakai class `.form-input`/`.form-label`/`.btn-\*` (nama field POST tidak berubah).

- ****Detail / Edit / Hapus**** (`detail_jurnal.php`, `edit_jurnal.php`, `proses_edit.php`, `hapus_jurnal.php`, `jurnal.php`): edit dapat mengganti foto (file lama dihapus); hapus membersihkan file foto; semua dibatasi milik sendiri (`WHERE id_jurnal=? AND id_siswa=?`, selain itu `abort`).

- ****Halaman agregasi**** (`siswa/kelas.php`, `siswa/absensi.php`, `siswa/laporan.php`, `siswa/profil.php`) — lihat §4.6.

**### 4.6 Halaman Agregasi Kelas / Absensi / Laporan / Profil (read-only, tanpa tabel baru)**

12 file (4 per role), semua via layout + `require_login` + prepared statement + `htmlspecialchars`. Tidak ada operasi tulis; tidak ada perubahan skema.

| Halaman | Admin | Guru | Siswa |

|---|---|---|---|

| `kelas.php` | `GROUP BY kelas`: jumlah siswa, total jurnal, jurnal terakhir per kelas | Sebaran kelas siswa bimbingannya (`WHERE id_guru=?`) | Kelas sendiri + daftar teman sekelas |

| `absensi.php` | Per siswa: `COUNT(DISTINCT tanggal_kegiatan)` = hari hadir + tanggal terakhir mengisi | Sama, hanya siswa bimbingan | Milik sendiri, diringkas per bulan |

| `laporan.php` | Pintu masuk: kartu menuju Data Jurnal & Data Bimbingan | Pintu masuk: Cetak Laporan + Jurnal + Riwayat | Ringkasan sendiri + tombol Cetak (print-friendly) |

| `profil.php` | Username + terdaftar + peran | NIP + nama + jumlah bimbingan | NISN, kelas, jurusan, DUDI, guru pembimbing |

**### 4.7 Design System & Layout (shared)**

- ****Token warna**** (`:root` di `head.php` + `assets/css/style.css`, override `.dark` tersedia): `--primary:#0EA5A0`, `--primary-dark:#0B7C6C`, `--primary-soft:#EFFAF8`, `--accent:#2563EB`, `--background:#F4F7F9`, `--surface:#FFFFFF`, `--text:#0F172A`, `--muted:#64748B`, `--border:#E6EDF2`, `--success:#16A34A`, `--warning:#D97706`, `--danger:#DC2626`, `--radius:16px`. Tailwind config memetakan `primary` ke palette teal + `boxShadow\.card` ringan.

- ****Mapping class → fungsi:**** `.card` (panel putih, border halus, shadow ringan), `.stat-card` (hover lift), `.stat-icon/.stat-label/.stat-number/.stat-sub`, `.nav-link(.active)` (aktif = background teal + shadow), `.nav-section` (label seksi sidebar), `.btn-primary/.btn-ghost/.btn-danger`, `.form-input` (focus ring teal) + `.form-label` + `.form-hint`, `.table-medical` (header tint, hover baris) dalam `.table-wrap` (scroll horizontal mobile), `.hero-medical` (gradien `#0B7C6C→#0EA5A0→#2563EB`), `.pg-btn` (paginasi).

- ****Layout**** (`includes/layout.php`): sidebar fixed 268px berisi section Menu Utama/Data/Lainnya + user card + logout; topbar sticky berisi hamburger (mobile), judul + subtitle, chip nama user, theme toggle. ID JS dipertahankan: `sidebar`, `sidebarOverlay`, `sidebarToggle`, `themeToggle`, `confirmModal`, `lightbox`.

- ****Helpers:**** `ej_avatar_initial`, `ej_badge_status` (di `layout.php`); `UI_INPUT`/`UI_LABEL` (di `helpers.php`, kini teal + menumpang `.form-input`).

- ****Ikon:**** SVG inline stroke 1.8 konsisten; ****tanpa emoji sebagai ikon UI**** di halaman yang disentuh v1.4.

- ****Pencarian tabel generik**** via `data-table-filter` + ****paginasi generik**** via `data-paginate`/`data-paginate-nav`/`__ejPaginate()` (`assets/js/app.js`); halaman dengan filter gabungan memakai fungsi inline per halaman (teks AND nama AND status) yang menandai `data-filtered` agar akur dengan paginasi.

- ****Upload:**** input file dengan preview (`data-img-preview`), validasi tipe & ukuran di client dan server; file tersimpan di `uploads/jurnal/` dan `uploads/bimbingan/`.

- ****Font:**** Poppins (judul/angka, `font-family:'Poppins'`) + Inter (body).

---

**## 5. Kebutuhan Non-Fungsional**

| Aspek | Requirement |

|---|---|

| Keamanan | Password selalu di-hash (bcrypt via `password_hash`); seluruh query tulis/baca sensitif memakai prepared statement; proteksi kepemilikan data (siswa hanya menyentuh jurnalnya; guru hanya jurnal/bimbingan siswa bimbingannya — termasuk cegah akses via URL langsung); admin tidak bisa hapus akun sendiri; minimal 1 admin; edit password hanya via admin login |

| Validasi upload | Foto: JPG/JPEG/PNG, ≤ 2 MB; CSV import: `.csv`, ≤ 2 MB, ≤ 2000 baris |

| Performa | Tabel memakai paginasi client-side (8–10 baris/halaman); import massal dibatasi 2000 baris per proses |

| UX | Responsif mobile–desktop (sidebar → hamburger, tabel scroll horizontal, form 1 kolom); dark mode (toggle + auto); hero + stat cards; toast auto-dismiss ±6 detik; konfirmasi hapus via modal; lightbox foto; animasi ringan (hover/transition) |

| Kompatibilitas | XAMPP Windows, PHP 8.2, MySQL/MariaDB; koneksi di `config/koneksi.php` (host/user/pass/db); tanpa composer/npm |

| Keterbatasan yang disadari | Filter + paginasi tabel client-side (kembali default saat refresh); foto jurnal/bimbingan tidak ikut import CSV; template CSV memakai delimiter koma; halaman kelola sekunder (Siswa/Guru/Bimbingan/Import/Cetak/Detail) masih memakai aksen indigo lama di konten — dijadwalkan penyeragaman tahap berikut |

---

**## 6. Model Data (`jurnal`)**

| Tabel | Kolom kunci | Relasi |

|---|---|---|

| `admin` | `id_admin` PK AI, `username` UNIQUE, `kode_unik` (hash password), `created_at` | — |

| `guru` | `id_guru` PK AI, `nip` UNIQUE, `nama_guru`, `password` (hash), `created_at` | — |

| `siswa` | `id_siswa` PK AI, `nisn` UNIQUE, `nama_lengkap`, `kelas`, `jurusan`, `dudi_magang`, `id_guru`→`guru` NULL (belum ada pembimbing), `password` (hash), `created_at` | 1:1 ke `guru` (N:1 dari sisi guru: 1 guru → banyak siswa) |

| `jurnal` | `id_jurnal` PK AI, `id_siswa`→`siswa`, `tanggal_kegiatan` DATE, `nama_dudi`, `pembimbing`, `kegiatan_magang` TEXT, `keterangan` TEXT NULL, `status` ENUM(`Menunggu`,`Disetujui`,`Ditolak`) DEFAULT `Menunggu`, `foto` NULL, `created_at`, `updated_at` | N:1 ke `siswa` |

| `bimbingan` | `id_bimbingan` PK AI, `id_guru`→`guru`, `id_siswa`→`siswa`, `tanggal_bimbingan` DATE, `nama_dudi`, `keterangan` TEXT, `foto` NULL, `created_at` | N:1 ke `guru`, N:1 ke `siswa` |

Relasi FK (nama constraint di skema fresh): `fk_siswa_guru` (`siswa.id_guru → guru`, `ON DELETE SET NULL`), `fk_jurnal_siswa` (`ON DELETE CASCADE`), `fk_bimbingan_guru` + `fk_bimbingan_siswa` (`ON DELETE CASCADE`).

****Seed dev**** (`database/database.sql`): 1 admin, 2 guru, 3 siswa (terpasang ke pembimbing), 4 jurnal (tanggal relatif `CURDATE()` agar "Jurnal Hari Ini" terisi; status campur), 2 bimbingan.

Catatan instalasi lama: bila `jurnal.status` belum ada, guru mengaktifkannya sekali via `guru/setup_status.php`; bila `siswa.id_guru` belum ada, admin menjalankannya sekali via `admin/setup_pembimbing.php` (idempoten; nilai awal `NULL`). ****Instalasi fresh tidak memerlukan keduanya**** — skema `database/database.sql` sudah lengkap. File `admin/setup_hapus_pembimbing.php` deprecated, jangan dipakai.

---

**## 7. Alur Utama**

1\. ****Instalasi fresh:**** import `database/database.sql` (via phpMyAdmin atau `mysql.exe -u root < database\database.sql`) → buka `tes_koneksi.php` ("Koneksi database berhasil!") → login dengan akun seed §3.

2\. ****Login:**** `index.php` → `auth/login.php` → pilih role → dashboard sesuai role.

3\. ****Siswa harian:**** Dashboard (lihat guru pembimbing) → Isi Jurnal (+foto) → riwayat → Detail/Edit/Hapus bila perlu revisi.

4\. ****Guru:**** Dashboard (statistik bimbingan sendiri) → Jurnal Siswa Bimbingan → Setujui/Revisi → Tambah Bimbingan untuk siswa bimbingannya (+foto) → Riwayat → Cetak Laporan (hanya bimbingan, print).

5\. ****Admin onboarding massal:**** pastikan Guru dulu → Import Siswa dengan `nip_pembimbing` (atau Tambah manual + pilih pembimbing) → periksa laporan hasil → lengkapi yang "Belum ada" via Edit.

6\. ****Lupa kredensial:**** user lapor ke admin → admin cari nama di Kelola Siswa/Guru (NISN/NIP tampil jelas di tabel) → Edit → koreksi NISN/NIP dan/atau ketik password baru langsung → Simpan → sampaikan ke user.

7\. ****Rekap agregat:**** semua role dapat membuka menu Kelas / Absensi / Laporan / Profil (read-only, scope sesuai role) tanpa mengubah data.

---

**## 8. Struktur File**

```

e-jurnal-smk/

├── index.php                    → redirect ke auth/login.php

├── config/koneksi.php           → koneksi mysqli ($conn), database `jurnal`

├── database/database.sql        → skema fresh + seed akun dev (import awal)

├── auth/                        → login.php (split hero + form card), proses_login.php, logout.php

├── includes/                    → head.php (token CSS, Tailwind primary teal, font Poppins/Inter),

│                                  layout.php (sidebar berseksi + topbar + helpers avatar/badge),

│                                  footer.php, toast.php, error.php,

│                                  helpers.php (require_login/abort/back_with_error/UI_\*),

│                                  import_helpers.php (parser CSV + config tipe)

├── assets/css/                  → style.css (design system: variables, card, nav, btn, form, table, hero, pagination)

├── assets/js/                   → app.js (sidebar/modal/lightbox/filter generik/paginasi data-paginate),

│                                  theme.js (dark mode)

├── assets/img/                  → Logo-SMK-Negeri-Purwosari.png (logo sidebar/login/favicon)

├── backup/                      → dump .sql lama (DB `e_jurnal_smk`) + arsip uploads .zip + restore.txt

├── uploads/jurnal/              → foto bukti jurnal

├── uploads/bimbingan/           → foto dokumentasi bimbingan

├── admin/                       → dashboard.php (hero + 4 stat cards), siswa.php, guru.php, admin.php,

│                                  jurnal.php (kolom No + paginasi), bimbingan.php, import.php,

│                                  kelas.php, absensi.php, laporan.php, profil.php (agregasi read-only),

│                                  tambah_siswa.php, proses_tambah_siswa.php,

│                                  tambah_guru.php, proses_tambah_guru.php,

│                                  edit_siswa.php, proses_edit_siswa.php,

│                                  edit_guru.php, proses_edit_guru.php,

│                                  hapus_siswa.php, hapus_guru.php,

│                                  setup_pembimbing.php (legacy: hanya instalasi lama),

│                                  setup_hapus_pembimbing.php (deprecated — jangan dipakai),

│                                  proses_import.php, download_template.php

├── guru/                        → dashboard.php (hero + 3 stat cards), jurnal_semua.php (No + paginasi),

│                                  jurnal_siswa.php, verifikasi.php, setup_status.php (legacy),

│                                  bimbingan.php, tambah_bimbingan.php (form restyle),

│                                  proses_bimbingan.php, riwayat_bimbingan.php,

│                                  kelas.php, absensi.php, laporan.php, profil.php (agregasi read-only),

│                                  cetak_laporan.php

├── siswa/                       → dashboard.php (hero + 4 stat cards + riwayat paginasi),

│                                  jurnal.php, tambah_jurnal.php, edit_jurnal.php (form restyle),

│                                  proses_tambah.php, detail_jurnal.php,

│                                  proses_edit.php, hapus_jurnal.php,

│                                  kelas.php, absensi.php, laporan.php, profil.php (agregasi read-only)

├── buat_hash.php                → utilitas generate hash (dev)

└── tes_koneksi.php              → utilitas tes koneksi DB (dev)

```

---

**## 9. Batasan & Asumsi**

1\. Single-school deployment (satu database, tanpa multi-sekolah).

2\. Tidak ada registrasi mandiri — semua akun dibuat admin (manual/import) atau berasal dari seed dev.

3\. Tidak ada fitur lupa-password mandiri (self-service); penanganan via admin (lihat Alur §7.6).

4\. Foto tidak ikut dalam import CSV; foto hanya via form web.

5\. Filter + paginasi tabel bersifat client-side dan tidak tersimpan di URL (kecuali filter siswa di cetak laporan yang memakai GET `?siswa=`).

6\. Role admin diasumsikan dipercaya penuh (dapat mengubah password siapa pun).

7\. Halaman agregasi (§4.6) read-only — tidak menambah/mengubah tabel.

---

**## 10. Backlog / Pengembangan Lanjutan (di luar scope v1.4)**

- Penyeragaman aksen indigo → teal di halaman kelola sekunder (Siswa/Guru/Bimbingan/Import/Cetak/Detail).

- Ekspor data (Excel/PDF) untuk admin; rekap kehadiran/nilai PKL.

- Notifikasi (mis. jurnal baru untuk guru, hasil verifikasi untuk siswa).

- Paginasi server-side untuk tabel besar; filter tanggal rentang.

- Audit log aktivitas admin (tambah/edit/hapus/reset password).

- Kebijakan password (panjang minimum, wajib ganti berkala) + hapus akun seed di produksi.
