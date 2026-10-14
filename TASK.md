# TASK.md — Coding First LMS

## 2026-10-13 — Rombak total v2 (branch `rombak-total-v2`)

Permintaan pemilik: perbaiki semua yang rusak, landing page lebih elegan, rombak tampilan & seluruh
fitur dashboard, Settings lengkap (keputusan desain dibuat 2 versi yang bisa dipilih dari Settings),
patuh penuh UU No. 27/2022 (PDP) & peraturan pelaksananya (consent, DSR, privacy notice, DPA, dst.),
user-friendly, mobile-friendly, PWA, warna/logo/favicon baru.

### Keputusan (2026-10-13)
- Desain dipilih panel 3 desainer + juri: **Versi A = Sumi & Shu (Editorial)** — default; **Versi B = Asagi (Presisi)**.
  Toggle A/B di Settings: tema, logo (A/B/ikuti tema), landing, navigasi desktop (sidebar/topnav),
  navigasi ponsel (tab/laci), gaya KPI (strip/kartu), beranda siswa (tenang/ceria), halaman login (split/tengah),
  kerapatan, izin pengguna memilih tema.
- Dasar hukum: UU No. 27/2022 (berlaku penuh sejak 17 Okt 2024) + **PP No. 33 Tahun 2026** (diundangkan 16 Jul 2026,
  berlaku 16 Jan 2027). Checklist implementasi 68 butir dari riset.
- Audit 11 area: ±490 temuan (≈60 kritikal). Banyak fitur diganti stub "coming soon" di commit `fb45d9e`.

### Fondasi
- [x] Branch kerja `rombak-total-v2`
- [x] Migrasi kompatibel SQLite (test & dev lokal) — ganti `ALTER ... MODIFY` MySQL-only dengan `->change()`
- [x] Perbaiki factory/seeder kelas (`teacher_id` sudah dihapus → pivot `class_teacher`)
- [x] Smoke test semua route GET per role (`tests/Feature/Smoke/RouteSmokeTest.php`)
- [x] Pecah `routes/web.php` per area (`routes/web/*.php`), hapus route debug, perbaiki urutan route wildcard
- [x] Update dependensi PHP (patch 46 advisori keamanan, tetap Laravel 12.x)
- [x] Inti modul Settings (`settings` table, `SettingsManager`, helper `setting()`, `config/settings.php`)
- [ ] Design system: token warna (CSS variables, tema A/B), font self-host, ikon, komponen UI, layout shell A/B
- [ ] Logo A/B, favicon, ikon PWA, manifest, service worker, halaman offline
- [ ] Data demo kaya & konsisten (enrollment, jadwal, transaksi, relasi orang tua–anak, dll.)

### Modul baru
- [ ] Admin › Pengaturan lengkap (semua grup + toggle versi A/B)
- [ ] PDP: persetujuan (consent) + persetujuan orang tua/wali untuk anak, banner cookie, dokumen kebijakan berversi
- [ ] PDP: Pusat Privasi pengguna + DSR (akses, ekspor/portabilitas, koreksi, hapus/anonimkan, batasi, keberatan, tarik persetujuan)
- [ ] PDP: tata kelola — ROPA, register pemroses + DPA, DPIA, log insiden (3×24 jam), log audit, retensi otomatis, header keamanan

### Rombak per area (bug fix + UI baru + fitur placeholder diimplementasikan)
- [ ] Admin inti (user, role, kursus, kelas, enrollment, jadwal, dashboard)
- [ ] Akademik (kurikulum, silabus, materi, laporan)
- [ ] Portal guru
- [ ] Portal siswa
- [ ] Portal orang tua
- [ ] Keuangan
- [ ] Sertifikat, galeri, foto kelas, testimoni
- [ ] Gamifikasi & chat
- [ ] Auth & profil/akun
- [ ] Landing page versi A & B + halaman publik

### Verifikasi
- [ ] Seluruh test hijau (termasuk smoke test semua role)
- [ ] `npm run build` sukses
- [ ] Review keamanan & kepatuhan PDP
- [ ] Cek tampilan mobile di browser

## Discovered During Work
- `/courses`, `/contact` memanggil view yang tidak ada (500)
- Halaman profil crash untuk semua role (`isSubmitting` undefined di partial upload foto)
- `admin/classes/{id}` crash: relasi `teacher` sudah tidak ada (pakai `teachers`)
- `admin/syllabuses/{id}` crash: field array dicetak sebagai string
- `/parent/testimonials` → method `index` tidak ada; `/teacher/students` → view tidak ada
- `/student/courses` → kolom `status` ambigu pada query join
- `/certificates/create` & `/project-gallery/featured` tertelan route wildcard (urutan route)
- Seeder: tidak ada enrollment/jadwal/transaksi/relasi orang tua–anak karena semua kelas `planned`
- README berisi README bawaan Laravel; email akun demo di README (`.id`) tidak cocok dengan seeder (`.com`)
- Kredensial demo (superadmin, admin, finance) tercetak di halaman login produksi
- Admin bisa mengangkat diri jadi superadmin / mengambil alih akun superadmin
- Lampiran chat & foto anak di disk publik tanpa validasi tipe; `reply_to_id` membocorkan pesan percakapan lain
- Landing page memublikasikan nama, foto, poin anak tanpa persetujuan orang tua
- Modul sertifikat memakai library PDF/QR yang tidak terpasang (kini dipasang: dompdf + bacon-qr-code, QR berbentuk SVG)
- PHP lokal tanpa ekstensi `zip`/`gd`: composer jatuh ke git clone (perlu `php -d extension=zip`); 112 paket vendor lama berupa checkout git
