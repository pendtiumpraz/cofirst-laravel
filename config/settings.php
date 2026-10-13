<?php

/*
|--------------------------------------------------------------------------
| Application settings registry
|--------------------------------------------------------------------------
|
| Every configurable option of the app, rendered automatically on
| Admin > Pengaturan. Read values with setting('group.key').
|
| Field keys:
|   group    tab the field belongs to (see "groups")
|   type     text|textarea|email|url|tel|number|boolean|select|multiselect|
|            choice (A/B version cards)|color|image|markdown|list|repeater
|   label    Indonesian label;  help: optional hint text
|   default  value used until an admin saves something
|   rules    Laravel validation rules for the submitted value
|   options  for select/multiselect/choice: value => label (choice: value => [label, description])
|   fields   for repeater: sub-field name => label
|   section  optional sub-heading inside the tab
|
*/

$yesNo = 'boolean';

return [

    'groups' => [
        'general' => ['label' => 'Umum', 'icon' => 'building-office-2', 'description' => 'Identitas lembaga, kontak, zona waktu dan bahasa.'],
        'appearance' => ['label' => 'Tampilan & Versi', 'icon' => 'swatch', 'description' => 'Tema warna, logo, serta pilihan versi A/B untuk landing page, navigasi dashboard dan halaman login.'],
        'landing' => ['label' => 'Landing Page', 'icon' => 'globe-alt', 'description' => 'Konten halaman depan publik.'],
        'academic' => ['label' => 'Akademik', 'icon' => 'academic-cap', 'description' => 'Aturan kelas, absensi, laporan dan sertifikat.'],
        'finance' => ['label' => 'Keuangan', 'icon' => 'banknotes', 'description' => 'Mata uang, tagihan dan instruksi pembayaran.'],
        'gamification' => ['label' => 'Gamifikasi', 'icon' => 'trophy', 'description' => 'Poin, lencana dan papan peringkat.'],
        'notifications' => ['label' => 'Notifikasi & Email', 'icon' => 'bell', 'description' => 'Pengirim email dan notifikasi otomatis.'],
        'security' => ['label' => 'Keamanan', 'icon' => 'shield-check', 'description' => 'Pendaftaran, kata sandi, sesi dan proteksi login.'],
        'privacy' => ['label' => 'Privasi & PDP', 'icon' => 'finger-print', 'description' => 'Kepatuhan UU No. 27/2022 tentang Pelindungan Data Pribadi: pengendali data, DPO, persetujuan, retensi dan tenggat.'],
        'pwa' => ['label' => 'Aplikasi (PWA)', 'icon' => 'device-phone-mobile', 'description' => 'Pengaturan aplikasi yang dapat dipasang di ponsel.'],
        'integrations' => ['label' => 'Integrasi', 'icon' => 'puzzle-piece', 'description' => 'Layanan pihak ketiga (hanya aktif dengan persetujuan pengguna bila memproses data pribadi).'],
        'system' => ['label' => 'Sistem', 'icon' => 'server-stack', 'description' => 'Pengumuman, pemeliharaan dan informasi sistem.'],
    ],

    'fields' => [

        // ── Umum ───────────────────────────────────────────────────────────
        'general.site_name' => ['group' => 'general', 'type' => 'text', 'label' => 'Nama aplikasi', 'default' => 'Coding First', 'rules' => 'required|string|max:60'],
        'general.legal_name' => ['group' => 'general', 'type' => 'text', 'label' => 'Nama badan hukum', 'help' => 'Dipakai sebagai Pengendali Data Pribadi pada kebijakan privasi.', 'default' => 'Coding First Indonesia', 'rules' => 'required|string|max:120'],
        'general.tagline' => ['group' => 'general', 'type' => 'text', 'label' => 'Tagline', 'default' => 'Belajar coding bersama mentor dari perusahaan teknologi Jepang', 'rules' => 'nullable|string|max:140'],
        'general.description' => ['group' => 'general', 'type' => 'textarea', 'label' => 'Deskripsi singkat (SEO)', 'default' => 'Coding First adalah sekolah coding untuk anak, remaja dan dewasa dengan kurikulum terstruktur dan pengajar berpengalaman dari perusahaan Jepang.', 'rules' => 'nullable|string|max:300'],
        'general.contact_email' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'email', 'label' => 'Email kontak', 'default' => 'hello@codingfirst.id', 'rules' => 'required|email|max:120'],
        'general.contact_phone' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'tel', 'label' => 'Telepon', 'default' => '', 'rules' => 'nullable|string|max:30'],
        'general.whatsapp' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'tel', 'label' => 'Nomor WhatsApp', 'help' => 'Format internasional, contoh 6281234567890.', 'default' => '', 'rules' => 'nullable|regex:/^[0-9+ ]{8,20}$/'],
        'general.address' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'textarea', 'label' => 'Alamat', 'default' => 'Jakarta, Indonesia', 'rules' => 'nullable|string|max:300'],
        'general.maps_url' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'url', 'label' => 'Tautan Google Maps', 'default' => '', 'rules' => 'nullable|url|max:500'],
        'general.business_hours' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'text', 'label' => 'Jam operasional', 'default' => 'Senin–Sabtu, 09.00–18.00 WIB', 'rules' => 'nullable|string|max:120'],
        'general.social_instagram' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'Instagram', 'default' => '', 'rules' => 'nullable|url|max:255'],
        'general.social_youtube' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'YouTube', 'default' => '', 'rules' => 'nullable|url|max:255'],
        'general.social_tiktok' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'TikTok', 'default' => '', 'rules' => 'nullable|url|max:255'],
        'general.social_linkedin' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'LinkedIn', 'default' => '', 'rules' => 'nullable|url|max:255'],
        'general.timezone' => ['group' => 'general', 'section' => 'Regional', 'type' => 'select', 'label' => 'Zona waktu', 'default' => 'Asia/Jakarta', 'options' => ['Asia/Jakarta' => 'WIB (Asia/Jakarta)', 'Asia/Makassar' => 'WITA (Asia/Makassar)', 'Asia/Jayapura' => 'WIT (Asia/Jayapura)'], 'rules' => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
        'general.locale' => ['group' => 'general', 'section' => 'Regional', 'type' => 'select', 'label' => 'Bahasa default', 'default' => 'id', 'options' => ['id' => 'Bahasa Indonesia', 'en' => 'English'], 'rules' => 'required|in:id,en'],

        // ── Tampilan & Versi (A/B) ────────────────────────────────────────
        'appearance.theme' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Tema warna', 'default' => 'a', 'options' => ['a' => ['Versi A', 'Tema utama.'], 'b' => ['Versi B', 'Tema alternatif.']], 'rules' => 'required|in:a,b'],
        'appearance.logo_variant' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Gaya logo', 'default' => 'a', 'options' => ['a' => ['Versi A', 'Logo utama.'], 'b' => ['Versi B', 'Logo alternatif.']], 'rules' => 'required|in:a,b'],
        'appearance.landing_version' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Template landing page', 'default' => 'a', 'options' => ['a' => ['Versi A', 'Landing page utama.'], 'b' => ['Versi B', 'Landing page alternatif.']], 'rules' => 'required|in:a,b'],
        'appearance.dashboard_nav' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Navigasi dashboard', 'default' => 'sidebar', 'options' => ['sidebar' => ['Sidebar', 'Menu samping yang dapat diciutkan — cocok untuk banyak menu (admin).'], 'topnav' => ['Navigasi atas', 'Menu horizontal ringkas di atas — area konten lebih lebar.']], 'rules' => 'required|in:sidebar,topnav'],
        'appearance.auth_layout' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Tampilan halaman login & daftar', 'default' => 'split', 'options' => ['split' => ['Layar terbagi', 'Panel brand di sisi kiri, formulir di kanan.'], 'centered' => ['Kartu di tengah', 'Formulir ringkas di tengah layar.']], 'rules' => 'required|in:split,centered'],
        'appearance.color_mode' => ['group' => 'appearance', 'section' => 'Mode warna', 'type' => 'select', 'label' => 'Mode warna default', 'default' => 'system', 'options' => ['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Ikuti perangkat'], 'rules' => 'required|in:light,dark,system'],
        'appearance.allow_user_color_mode' => ['group' => 'appearance', 'section' => 'Mode warna', 'type' => $yesNo, 'label' => 'Izinkan pengguna memilih mode warna sendiri', 'default' => true, 'rules' => 'boolean'],
        'appearance.custom_logo' => ['group' => 'appearance', 'section' => 'Aset kustom', 'type' => 'image', 'label' => 'Logo kustom (opsional)', 'help' => 'SVG/PNG transparan. Mengganti logo bawaan bila diisi.', 'default' => null, 'rules' => 'nullable|file|mimes:svg,png,webp|max:1024'],
        'appearance.custom_logo_dark' => ['group' => 'appearance', 'section' => 'Aset kustom', 'type' => 'image', 'label' => 'Logo kustom untuk mode gelap', 'default' => null, 'rules' => 'nullable|file|mimes:svg,png,webp|max:1024'],

        // ── Landing page ──────────────────────────────────────────────────
        'landing.announcement' => ['group' => 'landing', 'type' => 'text', 'label' => 'Bilah pengumuman', 'help' => 'Kosongkan untuk menyembunyikan.', 'default' => '', 'rules' => 'nullable|string|max:160'],
        'landing.hero_title' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Judul utama', 'default' => 'Belajar coding dengan standar industri Jepang', 'rules' => 'required|string|max:120'],
        'landing.hero_subtitle' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'textarea', 'label' => 'Subjudul', 'default' => 'Kelas privat dan grup untuk anak, remaja dan dewasa — kurikulum terstruktur, mentor berpengalaman, dan laporan perkembangan yang transparan untuk orang tua.', 'rules' => 'nullable|string|max:300'],
        'landing.cta_primary_text' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tombol utama', 'default' => 'Daftar kelas percobaan', 'rules' => 'required|string|max:40'],
        'landing.cta_primary_url' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tautan tombol utama', 'default' => '/register', 'rules' => 'required|string|max:255'],
        'landing.cta_secondary_text' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tombol kedua', 'default' => 'Lihat program', 'rules' => 'nullable|string|max:40'],
        'landing.cta_secondary_url' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tautan tombol kedua', 'default' => '#program', 'rules' => 'nullable|string|max:255'],
        'landing.show_programs' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Program / kursus', 'default' => true, 'rules' => 'boolean'],
        'landing.show_teachers' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Pengajar', 'default' => true, 'rules' => 'boolean'],
        'landing.show_testimonials' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Testimoni orang tua', 'default' => true, 'rules' => 'boolean'],
        'landing.show_stats' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Angka pencapaian', 'default' => true, 'rules' => 'boolean'],
        'landing.show_faq' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Tanya jawab (FAQ)', 'default' => true, 'rules' => 'boolean'],
        'landing.show_top_students' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Siswa berprestasi', 'help' => 'Data anak termasuk data pribadi spesifik (UU PDP Pasal 4). Hanya siswa dengan persetujuan publikasi dari orang tua yang ditampilkan.', 'default' => false, 'rules' => 'boolean'],
        'landing.stats' => ['group' => 'landing', 'section' => 'Konten', 'type' => 'repeater', 'label' => 'Angka pencapaian', 'fields' => ['value' => 'Angka', 'label' => 'Keterangan'], 'default' => [['value' => '1.200+', 'label' => 'Siswa belajar'], ['value' => '12', 'label' => 'Program'], ['value' => '15+', 'label' => 'Mentor'], ['value' => '4,9/5', 'label' => 'Kepuasan orang tua']], 'rules' => 'nullable|array|max:6'],
        'landing.faq' => ['group' => 'landing', 'section' => 'Konten', 'type' => 'repeater', 'label' => 'Tanya jawab', 'fields' => ['question' => 'Pertanyaan', 'answer' => 'Jawaban'], 'default' => [
            ['question' => 'Usia berapa anak bisa mulai belajar?', 'answer' => 'Mulai usia 7 tahun dengan Scratch, lalu naik bertahap ke Python, web, dan game development.'],
            ['question' => 'Apakah kelas tersedia online?', 'answer' => 'Ya. Tersedia kelas online, privat ke rumah, dan kelas grup kecil.'],
            ['question' => 'Bagaimana orang tua memantau perkembangan?', 'answer' => 'Orang tua mendapat akun sendiri untuk melihat jadwal, laporan pertemuan, kehadiran, dan pembayaran.'],
            ['question' => 'Bagaimana data anak saya dilindungi?', 'answer' => 'Kami memproses data anak hanya dengan persetujuan orang tua/wali sesuai UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi. Lihat Kebijakan Privasi kami.'],
        ], 'rules' => 'nullable|array|max:20'],

        // ── Akademik ─────────────────────────────────────────────────────
        'academic.session_duration_minutes' => ['group' => 'academic', 'type' => 'number', 'label' => 'Durasi pertemuan default (menit)', 'default' => 90, 'rules' => 'required|integer|min:15|max:480'],
        'academic.attendance_late_minutes' => ['group' => 'academic', 'type' => 'number', 'label' => 'Toleransi terlambat (menit)', 'default' => 15, 'rules' => 'required|integer|min:0|max:120'],
        'academic.reports_visible_to_parents' => ['group' => 'academic', 'type' => $yesNo, 'label' => 'Laporan pertemuan terlihat oleh orang tua', 'default' => true, 'rules' => 'boolean'],
        'academic.certificate_prefix' => ['group' => 'academic', 'section' => 'Sertifikat', 'type' => 'text', 'label' => 'Prefiks nomor sertifikat', 'default' => 'CF', 'rules' => 'required|alpha_dash|max:10'],
        'academic.certificate_signer_name' => ['group' => 'academic', 'section' => 'Sertifikat', 'type' => 'text', 'label' => 'Nama penandatangan', 'default' => 'Direktur Akademik', 'rules' => 'nullable|string|max:80'],
        'academic.certificate_signer_title' => ['group' => 'academic', 'section' => 'Sertifikat', 'type' => 'text', 'label' => 'Jabatan penandatangan', 'default' => 'Coding First', 'rules' => 'nullable|string|max:80'],

        // ── Keuangan ─────────────────────────────────────────────────────
        'finance.currency' => ['group' => 'finance', 'type' => 'select', 'label' => 'Mata uang', 'default' => 'IDR', 'options' => ['IDR' => 'Rupiah (IDR)'], 'rules' => 'required|in:IDR'],
        'finance.invoice_prefix' => ['group' => 'finance', 'type' => 'text', 'label' => 'Prefiks nomor tagihan', 'default' => 'INV', 'rules' => 'required|alpha_dash|max:10'],
        'finance.payment_due_days' => ['group' => 'finance', 'type' => 'number', 'label' => 'Jatuh tempo (hari)', 'default' => 7, 'rules' => 'required|integer|min:0|max:90'],
        'finance.tax_percent' => ['group' => 'finance', 'type' => 'number', 'label' => 'Pajak (%)', 'default' => 0, 'rules' => 'required|numeric|min:0|max:100'],
        'finance.bank_accounts' => ['group' => 'finance', 'type' => 'repeater', 'label' => 'Rekening pembayaran', 'fields' => ['bank' => 'Bank', 'number' => 'No. rekening', 'holder' => 'Atas nama'], 'default' => [], 'rules' => 'nullable|array|max:5'],
        'finance.payment_instructions' => ['group' => 'finance', 'type' => 'textarea', 'label' => 'Instruksi pembayaran', 'default' => 'Transfer sesuai nominal tagihan lalu unggah/kirim bukti pembayaran ke admin.', 'rules' => 'nullable|string|max:1000'],

        // ── Gamifikasi ───────────────────────────────────────────────────
        'gamification.enabled' => ['group' => 'gamification', 'type' => $yesNo, 'label' => 'Aktifkan gamifikasi', 'default' => true, 'rules' => 'boolean'],
        'gamification.points_login' => ['group' => 'gamification', 'type' => 'number', 'label' => 'Poin login harian', 'default' => 5, 'rules' => 'required|integer|min:0|max:1000'],
        'gamification.points_attendance' => ['group' => 'gamification', 'type' => 'number', 'label' => 'Poin kehadiran', 'default' => 10, 'rules' => 'required|integer|min:0|max:1000'],
        'gamification.points_assignment' => ['group' => 'gamification', 'type' => 'number', 'label' => 'Poin tugas selesai', 'default' => 20, 'rules' => 'required|integer|min:0|max:1000'],
        'gamification.leaderboard_visibility' => ['group' => 'gamification', 'type' => 'select', 'label' => 'Papan peringkat terlihat oleh', 'default' => 'members', 'options' => ['off' => 'Nonaktif', 'class' => 'Teman sekelas saja', 'members' => 'Semua pengguna yang login'], 'rules' => 'required|in:off,class,members'],
        'gamification.leaderboard_name_display' => ['group' => 'gamification', 'type' => 'select', 'label' => 'Nama siswa di papan peringkat', 'help' => 'Untuk melindungi data anak, gunakan nama depan atau inisial.', 'default' => 'first_name', 'options' => ['first_name' => 'Nama depan', 'initials' => 'Inisial', 'full' => 'Nama lengkap'], 'rules' => 'required|in:first_name,initials,full'],

        // ── Notifikasi & Email ───────────────────────────────────────────
        'notifications.from_name' => ['group' => 'notifications', 'type' => 'text', 'label' => 'Nama pengirim email', 'default' => 'Coding First', 'rules' => 'required|string|max:80'],
        'notifications.from_address' => ['group' => 'notifications', 'type' => 'email', 'label' => 'Alamat pengirim email', 'default' => 'no-reply@codingfirst.id', 'rules' => 'required|email|max:120'],
        'notifications.admin_email' => ['group' => 'notifications', 'type' => 'email', 'label' => 'Email admin penerima notifikasi', 'default' => 'admin@codingfirst.id', 'rules' => 'required|email|max:120'],
        'notifications.enrollment_created' => ['group' => 'notifications', 'section' => 'Notifikasi otomatis', 'type' => $yesNo, 'label' => 'Pendaftaran kelas baru', 'default' => true, 'rules' => 'boolean'],
        'notifications.payment_reminder' => ['group' => 'notifications', 'section' => 'Notifikasi otomatis', 'type' => $yesNo, 'label' => 'Pengingat pembayaran', 'default' => true, 'rules' => 'boolean'],
        'notifications.report_published' => ['group' => 'notifications', 'section' => 'Notifikasi otomatis', 'type' => $yesNo, 'label' => 'Laporan pertemuan terbit', 'default' => true, 'rules' => 'boolean'],
        'notifications.privacy_alerts' => ['group' => 'notifications', 'section' => 'Notifikasi otomatis', 'type' => $yesNo, 'label' => 'Peringatan tenggat permintaan data & insiden', 'default' => true, 'rules' => 'boolean'],

        // ── Keamanan ─────────────────────────────────────────────────────
        'security.registration_enabled' => ['group' => 'security', 'section' => 'Pendaftaran', 'type' => $yesNo, 'label' => 'Izinkan pendaftaran mandiri', 'default' => true, 'rules' => 'boolean'],
        'security.registration_roles' => ['group' => 'security', 'section' => 'Pendaftaran', 'type' => 'multiselect', 'label' => 'Peran yang boleh mendaftar sendiri', 'default' => ['student', 'parent'], 'options' => ['student' => 'Siswa', 'parent' => 'Orang tua'], 'rules' => 'array', 'item_rules' => 'in:student,parent'],
        'security.require_email_verification' => ['group' => 'security', 'section' => 'Pendaftaran', 'type' => $yesNo, 'label' => 'Wajib verifikasi email', 'default' => false, 'rules' => 'boolean'],
        'security.password_min_length' => ['group' => 'security', 'section' => 'Kata sandi', 'type' => 'number', 'label' => 'Panjang minimum kata sandi', 'default' => 8, 'rules' => 'required|integer|min:8|max:64'],
        'security.password_require_mixed_case' => ['group' => 'security', 'section' => 'Kata sandi', 'type' => $yesNo, 'label' => 'Wajib huruf besar & kecil', 'default' => true, 'rules' => 'boolean'],
        'security.password_require_numbers' => ['group' => 'security', 'section' => 'Kata sandi', 'type' => $yesNo, 'label' => 'Wajib angka', 'default' => true, 'rules' => 'boolean'],
        'security.password_require_symbols' => ['group' => 'security', 'section' => 'Kata sandi', 'type' => $yesNo, 'label' => 'Wajib simbol', 'default' => false, 'rules' => 'boolean'],
        'security.login_max_attempts' => ['group' => 'security', 'section' => 'Sesi & login', 'type' => 'number', 'label' => 'Maksimum percobaan login per menit', 'default' => 5, 'rules' => 'required|integer|min:3|max:20'],
        'security.session_lifetime_minutes' => ['group' => 'security', 'section' => 'Sesi & login', 'type' => 'number', 'label' => 'Durasi sesi (menit)', 'default' => 120, 'rules' => 'required|integer|min:15|max:10080'],
        'security.security_headers' => ['group' => 'security', 'section' => 'Sesi & login', 'type' => $yesNo, 'label' => 'Kirim header keamanan HTTP', 'default' => true, 'rules' => 'boolean'],

        // ── Privasi & PDP (UU No. 27 Tahun 2022) ─────────────────────────
        'privacy.controller_name' => ['group' => 'privacy', 'section' => 'Pengendali data', 'type' => 'text', 'label' => 'Nama Pengendali Data Pribadi', 'default' => 'Coding First Indonesia', 'rules' => 'required|string|max:120'],
        'privacy.controller_address' => ['group' => 'privacy', 'section' => 'Pengendali data', 'type' => 'textarea', 'label' => 'Alamat Pengendali', 'default' => 'Jakarta, Indonesia', 'rules' => 'required|string|max:300'],
        'privacy.dpo_name' => ['group' => 'privacy', 'section' => 'Pejabat Pelindungan Data Pribadi (DPO)', 'type' => 'text', 'label' => 'Nama pejabat/petugas PDP', 'default' => 'Petugas Pelindungan Data Pribadi', 'rules' => 'required|string|max:120'],
        'privacy.dpo_email' => ['group' => 'privacy', 'section' => 'Pejabat Pelindungan Data Pribadi (DPO)', 'type' => 'email', 'label' => 'Email DPO', 'default' => 'privasi@codingfirst.id', 'rules' => 'required|email|max:120'],
        'privacy.dpo_phone' => ['group' => 'privacy', 'section' => 'Pejabat Pelindungan Data Pribadi (DPO)', 'type' => 'tel', 'label' => 'Telepon DPO', 'default' => '', 'rules' => 'nullable|string|max:30'],
        'privacy.cookie_banner_enabled' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => $yesNo, 'label' => 'Tampilkan banner persetujuan cookie', 'default' => true, 'rules' => 'boolean'],
        'privacy.cookie_banner_text' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => 'textarea', 'label' => 'Teks banner cookie', 'default' => 'Kami memakai cookie yang diperlukan agar situs berfungsi. Dengan persetujuan Anda, kami juga memakai cookie opsional untuk preferensi dan analitik. Anda dapat mengubah pilihan kapan saja.', 'rules' => 'required|string|max:500'],
        'privacy.child_age_threshold' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => 'number', 'label' => 'Batas usia anak (tahun)', 'help' => 'Di bawah usia ini, pemrosesan data memerlukan persetujuan orang tua/wali (UU PDP Pasal 25).', 'default' => 18, 'rules' => 'required|integer|min:13|max:21'],
        'privacy.require_parental_consent' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => $yesNo, 'label' => 'Wajib persetujuan orang tua/wali untuk anak', 'default' => true, 'rules' => 'boolean'],
        'privacy.policy_reacceptance_required' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => $yesNo, 'label' => 'Minta persetujuan ulang saat kebijakan berubah', 'default' => true, 'rules' => 'boolean'],
        'privacy.dsr_sla_hours' => ['group' => 'privacy', 'section' => 'Tenggat', 'type' => 'number', 'label' => 'Tenggat respons permintaan subjek data (jam)', 'help' => 'UU PDP umumnya menetapkan paling lambat 3 x 24 jam.', 'default' => 72, 'rules' => 'required|integer|min:24|max:720'],
        'privacy.breach_notification_hours' => ['group' => 'privacy', 'section' => 'Tenggat', 'type' => 'number', 'label' => 'Tenggat notifikasi kegagalan pelindungan data (jam)', 'help' => 'UU PDP Pasal 46: paling lambat 3 x 24 jam.', 'default' => 72, 'rules' => 'required|integer|min:24|max:72'],
        'privacy.retention_inactive_account_months' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Anonimkan akun tidak aktif setelah (bulan)', 'default' => 24, 'rules' => 'required|integer|min:6|max:120'],
        'privacy.retention_audit_log_days' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Simpan log audit selama (hari)', 'default' => 365, 'rules' => 'required|integer|min:90|max:3650'],
        'privacy.retention_financial_years' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Simpan catatan keuangan selama (tahun)', 'help' => 'Kewajiban penyimpanan dokumen keuangan/perpajakan umumnya 10 tahun.', 'default' => 10, 'rules' => 'required|integer|min:5|max:30'],
        'privacy.retention_chat_months' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Simpan pesan chat selama (bulan)', 'default' => 24, 'rules' => 'required|integer|min:1|max:120'],
        'privacy.retention_export_days' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Hapus berkas ekspor data setelah (hari)', 'default' => 7, 'rules' => 'required|integer|min:1|max:30'],
        'privacy.erasure_mode' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'select', 'label' => 'Cara penghapusan akun', 'default' => 'anonymize', 'options' => ['anonymize' => 'Anonimkan (catatan keuangan tetap tersimpan sesuai kewajiban hukum)', 'delete' => 'Hapus permanen bila tidak ada kewajiban retensi'], 'rules' => 'required|in:anonymize,delete'],
        'privacy.show_student_names_public' => ['group' => 'privacy', 'section' => 'Publikasi', 'type' => $yesNo, 'label' => 'Tampilkan nama siswa di halaman publik', 'help' => 'Tetap hanya untuk siswa yang orang tuanya memberi persetujuan publikasi.', 'default' => false, 'rules' => 'boolean'],

        // ── PWA ──────────────────────────────────────────────────────────
        'pwa.enabled' => ['group' => 'pwa', 'type' => $yesNo, 'label' => 'Aktifkan aplikasi yang dapat dipasang (PWA)', 'default' => true, 'rules' => 'boolean'],
        'pwa.name' => ['group' => 'pwa', 'type' => 'text', 'label' => 'Nama aplikasi', 'default' => 'Coding First', 'rules' => 'required|string|max:45'],
        'pwa.short_name' => ['group' => 'pwa', 'type' => 'text', 'label' => 'Nama pendek (di bawah ikon)', 'default' => 'CoFirst', 'rules' => 'required|string|max:12'],
        'pwa.description' => ['group' => 'pwa', 'type' => 'text', 'label' => 'Deskripsi', 'default' => 'Portal belajar Coding First untuk siswa, orang tua dan pengajar.', 'rules' => 'nullable|string|max:160'],
        'pwa.background_color' => ['group' => 'pwa', 'type' => 'color', 'label' => 'Warna latar splash screen', 'default' => '#ffffff', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/'],
        'pwa.display' => ['group' => 'pwa', 'type' => 'select', 'label' => 'Mode tampilan', 'default' => 'standalone', 'options' => ['standalone' => 'Standalone (seperti aplikasi)', 'minimal-ui' => 'Minimal UI', 'browser' => 'Browser biasa'], 'rules' => 'required|in:standalone,minimal-ui,browser'],
        'pwa.install_prompt' => ['group' => 'pwa', 'type' => $yesNo, 'label' => 'Tampilkan ajakan pasang aplikasi', 'default' => true, 'rules' => 'boolean'],

        // ── Integrasi ────────────────────────────────────────────────────
        'integrations.whatsapp_button' => ['group' => 'integrations', 'type' => $yesNo, 'label' => 'Tombol WhatsApp mengambang di situs publik', 'default' => false, 'rules' => 'boolean'],
        'integrations.maps_embed' => ['group' => 'integrations', 'type' => $yesNo, 'label' => 'Sematkan peta Google Maps (memuat setelah persetujuan cookie)', 'default' => false, 'rules' => 'boolean'],
        'integrations.analytics_measurement_id' => ['group' => 'integrations', 'type' => 'text', 'label' => 'ID analitik (opsional)', 'help' => 'Skrip analitik hanya dimuat setelah pengunjung menyetujui kategori Analitik.', 'default' => '', 'rules' => 'nullable|string|max:40'],
        'integrations.default_meeting_platform' => ['group' => 'integrations', 'type' => 'select', 'label' => 'Platform kelas online default', 'default' => 'google_meet', 'options' => ['google_meet' => 'Google Meet', 'zoom' => 'Zoom', 'teams' => 'Microsoft Teams'], 'rules' => 'required|in:google_meet,zoom,teams'],

        // ── Sistem ───────────────────────────────────────────────────────
        'system.dashboard_announcement' => ['group' => 'system', 'type' => 'text', 'label' => 'Pengumuman di semua dashboard', 'help' => 'Kosongkan untuk menyembunyikan.', 'default' => '', 'rules' => 'nullable|string|max:200'],
        'system.maintenance_notice' => ['group' => 'system', 'type' => 'text', 'label' => 'Pemberitahuan pemeliharaan terjadwal', 'default' => '', 'rules' => 'nullable|string|max:200'],
    ],
];
