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
|   rules    Laravel validation rules for the submitted value (string with "|" or an
|            array — use an array whenever a regex contains "|")
|   item_rules  multiselect: rules for every selected value;
|               repeater: sub-field name => rules for every row
|   options  for select/multiselect/choice: value => label (choice: value => [label, description])
|   fields   for repeater: sub-field name => label, or => ['label' => …, 'type' => text|textarea|url|number]
|   section  optional sub-heading inside the tab
|   placeholder / unit (suffix shown in number inputs) — optional
|
| Group keys:
|   label, icon (heroicon outline name), description
|   edit_roles        roles that may change the group (default: admin + superadmin);
|                     everyone with access to Admin > Pengaturan can still view it
|   confirm_password  editors must re-enter their password before changing the group
|
| Security: image fields never accept SVG (script inside SVG = stored XSS) and link
| fields only accept "/path", "#anchor" or https:// URLs (no javascript: links).
|
*/

// Reason: a CTA link may point to a page of this site, an anchor or an https URL — never javascript:/data:.
$safeLink = 'regex:/^(\/(?!\/)|#|https:\/\/)/';
$httpUrl = 'url:http,https';
$logoRules = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'];

$yesNo = 'boolean';

return [

    'groups' => [
        'general' => [
            'label' => 'Umum',
            'icon' => 'building-office-2',
            'description' => 'Identitas lembaga, kontak, zona waktu dan bahasa.',
            'sections' => [
                'Identitas' => 'Nama dan deskripsi yang tampil di judul halaman, email dan hasil mesin pencari.',
                'Kontak' => 'Ditampilkan di footer situs publik dan halaman kontak.',
                'Media sosial' => 'Kosongkan akun yang tidak dipakai; tautannya disembunyikan.',
                'Regional' => 'Dipakai untuk semua tanggal, jam dan pesan sistem.',
            ],
        ],
        'appearance' => [
            'label' => 'Tampilan & Versi',
            'icon' => 'swatch',
            'description' => 'Tema warna, logo, serta pilihan versi A/B untuk landing page, navigasi dashboard dan halaman login.',
            'sections' => [
                'Versi A/B' => 'Setiap keputusan desain tersedia dalam dua versi. Pratinjau hanya terlihat oleh Anda sampai disimpan.',
                'Kerapatan & mode warna' => 'Nilai bawaan untuk semua pengguna. Pengguna dapat mengubahnya di Preferensi bila diizinkan.',
                'Aset kustom' => 'Opsional. Logo bawaan sudah dioptimalkan untuk kedua versi dan mode gelap.',
            ],
        ],
        'landing' => [
            'label' => 'Landing Page',
            'icon' => 'globe-alt',
            'description' => 'Konten halaman depan publik.',
            'sections' => [
                'Pengumuman' => 'Bilah tipis di bagian paling atas halaman depan.',
                'Hero' => 'Bagian pertama yang dilihat pengunjung.',
                'Bagian yang ditampilkan' => 'Sembunyikan bagian yang belum siap ditampilkan.',
                'Konten' => 'Gunakan angka dan jawaban yang sebenarnya — klaim di halaman publik harus dapat dipertanggungjawabkan.',
            ],
        ],
        'academic' => [
            'label' => 'Akademik',
            'icon' => 'academic-cap',
            'description' => 'Aturan kelas, absensi, laporan dan sertifikat.',
            'sections' => [
                'Kelas & kehadiran' => 'Aturan bawaan untuk kelas baru dan pencatatan kehadiran.',
                'Sertifikat' => 'Dicetak pada setiap sertifikat yang diterbitkan.',
            ],
        ],
        'finance' => [
            'label' => 'Keuangan',
            'icon' => 'banknotes',
            'description' => 'Mata uang, tagihan dan instruksi pembayaran.',
            'sections' => [
                'Tagihan' => 'Format nomor tagihan, jatuh tempo dan pajak.',
                'Pembayaran' => 'Ditampilkan kepada orang tua dan siswa pada halaman tagihan.',
            ],
        ],
        'gamification' => [
            'label' => 'Gamifikasi',
            'icon' => 'trophy',
            'description' => 'Poin, lencana dan papan peringkat.',
            'sections' => [
                'Poin' => 'Poin yang diberikan otomatis untuk setiap aktivitas.',
                'Papan peringkat' => 'Demi melindungi data anak, tampilkan nama seminimal mungkin.',
            ],
        ],
        'notifications' => [
            'label' => 'Notifikasi & Email',
            'icon' => 'bell',
            'description' => 'Pengirim email dan notifikasi otomatis.',
            'sections' => [
                'Pengirim email' => 'Identitas pengirim semua email sistem.',
                'Notifikasi otomatis' => 'Pengguna tetap dapat menonaktifkan email tertentu di Preferensi.',
            ],
        ],
        'security' => [
            'label' => 'Keamanan',
            'icon' => 'shield-check',
            'description' => 'Pendaftaran, kata sandi, sesi dan proteksi login.',
            'edit_roles' => ['superadmin'],
            'confirm_password' => true,
            'sections' => [
                'Pendaftaran' => 'Siapa yang boleh membuat akun sendiri.',
                'Kata sandi' => 'Berlaku untuk kata sandi baru dan saat pengguna mengganti kata sandi.',
                'Sesi & login' => 'Perlindungan terhadap tebakan kata sandi dan sesi yang ditinggalkan.',
            ],
        ],
        'privacy' => [
            'label' => 'Privasi & PDP',
            'icon' => 'finger-print',
            'description' => 'Kepatuhan UU No. 27/2022 tentang Pelindungan Data Pribadi: pengendali data, DPO, persetujuan, retensi dan tenggat.',
            'edit_roles' => ['superadmin'],
            'confirm_password' => true,
            'sections' => [
                'Pengendali data' => 'Identitas lembaga yang dicantumkan pada Kebijakan Privasi.',
                'Pejabat Pelindungan Data Pribadi (DPO)' => 'Kontak yang dapat dihubungi subjek data untuk pertanyaan dan permintaan.',
                'Persetujuan' => 'Cara persetujuan diminta, termasuk persetujuan orang tua/wali untuk anak.',
                'Tenggat' => 'Batas waktu yang ditampilkan dan dipantau di modul Privasi.',
                'Retensi' => 'Data dihapus atau dianonimkan otomatis setelah masa simpan berakhir.',
                'Publikasi' => 'Pengaturan bawaan yang melindungi data anak di halaman publik.',
            ],
        ],
        'pwa' => [
            'label' => 'Aplikasi (PWA)',
            'icon' => 'device-phone-mobile',
            'description' => 'Pengaturan aplikasi yang dapat dipasang di ponsel.',
            'sections' => [
                'Pemasangan' => 'Aplikasi dapat dipasang dari peramban ponsel tanpa toko aplikasi.',
                'Identitas aplikasi' => 'Nama yang tampil di layar utama ponsel.',
                'Tampilan aplikasi' => 'Warna layar pembuka dan cara aplikasi dibuka.',
            ],
        ],
        'integrations' => [
            'label' => 'Integrasi',
            'icon' => 'puzzle-piece',
            'description' => 'Layanan pihak ketiga (hanya aktif dengan persetujuan pengguna bila memproses data pribadi).',
            'sections' => [
                'Situs publik' => 'Fitur pihak ketiga di situs publik — dimuat hanya setelah persetujuan cookie bila memproses data pribadi.',
                'Analitik' => 'Kosongkan untuk menonaktifkan analitik sepenuhnya.',
                'Kelas online' => 'Platform bawaan saat jadwal kelas online dibuat.',
            ],
        ],
        'system' => [
            'label' => 'Sistem',
            'icon' => 'server-stack',
            'description' => 'Pengumuman, pemeliharaan dan informasi sistem.',
            'sections' => [
                'Pemberitahuan' => 'Bilah informasi di atas semua dashboard. Kosongkan untuk menyembunyikan.',
            ],
        ],
    ],

    'fields' => [

        // ── Umum ───────────────────────────────────────────────────────────
        'general.site_name' => ['group' => 'general', 'section' => 'Identitas', 'type' => 'text', 'label' => 'Nama aplikasi', 'default' => 'Coding First', 'rules' => 'required|string|max:60'],
        'general.legal_name' => ['group' => 'general', 'section' => 'Identitas', 'type' => 'text', 'label' => 'Nama badan hukum', 'help' => 'Dipakai sebagai Pengendali Data Pribadi pada kebijakan privasi.', 'default' => 'Coding First Indonesia', 'rules' => 'required|string|max:120'],
        'general.tagline' => ['group' => 'general', 'section' => 'Identitas', 'type' => 'text', 'label' => 'Tagline', 'default' => 'Belajar coding bersama mentor dari perusahaan teknologi Jepang', 'rules' => 'nullable|string|max:140'],
        'general.description' => ['group' => 'general', 'section' => 'Identitas', 'type' => 'textarea', 'label' => 'Deskripsi singkat (SEO)', 'default' => 'Coding First adalah sekolah coding untuk anak, remaja dan dewasa dengan kurikulum terstruktur dan pengajar berpengalaman dari perusahaan Jepang.', 'rules' => 'nullable|string|max:300'],
        'general.contact_email' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'email', 'label' => 'Email kontak', 'default' => 'hello@codingfirst.id', 'rules' => 'required|email|max:120'],
        'general.contact_phone' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'tel', 'label' => 'Telepon', 'default' => '', 'rules' => 'nullable|string|max:30'],
        'general.whatsapp' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'tel', 'label' => 'Nomor WhatsApp', 'help' => 'Format internasional, contoh 6281234567890.', 'placeholder' => '6281234567890', 'default' => '', 'rules' => ['nullable', 'regex:/^[0-9+ ]{8,20}$/']],
        'general.address' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'textarea', 'label' => 'Alamat', 'default' => 'Jakarta, Indonesia', 'rules' => 'nullable|string|max:300'],
        'general.maps_url' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'url', 'label' => 'Tautan Google Maps', 'default' => '', 'rules' => ['nullable', $httpUrl, 'max:500']],
        'general.business_hours' => ['group' => 'general', 'section' => 'Kontak', 'type' => 'text', 'label' => 'Jam operasional', 'default' => 'Senin–Sabtu, 09.00–18.00 WIB', 'rules' => 'nullable|string|max:120'],
        'general.social_instagram' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'Instagram', 'default' => '', 'rules' => ['nullable', $httpUrl, 'max:255']],
        'general.social_youtube' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'YouTube', 'default' => '', 'rules' => ['nullable', $httpUrl, 'max:255']],
        'general.social_tiktok' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'TikTok', 'default' => '', 'rules' => ['nullable', $httpUrl, 'max:255']],
        'general.social_linkedin' => ['group' => 'general', 'section' => 'Media sosial', 'type' => 'url', 'label' => 'LinkedIn', 'default' => '', 'rules' => ['nullable', $httpUrl, 'max:255']],
        'general.timezone' => ['group' => 'general', 'section' => 'Regional', 'type' => 'select', 'label' => 'Zona waktu', 'default' => 'Asia/Jakarta', 'options' => ['Asia/Jakarta' => 'WIB (Asia/Jakarta)', 'Asia/Makassar' => 'WITA (Asia/Makassar)', 'Asia/Jayapura' => 'WIT (Asia/Jayapura)'], 'rules' => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
        'general.locale' => ['group' => 'general', 'section' => 'Regional', 'type' => 'select', 'label' => 'Bahasa default', 'default' => 'id', 'options' => ['id' => 'Bahasa Indonesia', 'en' => 'English'], 'rules' => 'required|in:id,en'],

        // ── Tampilan & Versi (A/B) ────────────────────────────────────────
        'appearance.theme' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Tema warna & tipografi', 'default' => 'a', 'options' => [
            'a' => ['Versi A · Sumi & Shu (Editorial)', 'Kertas washi hangat, tinta sumi, biru aizome untuk setiap aksi dan stempel hanko merah sebagai tanda tangan brand. Judul serif Newsreader + Plus Jakarta Sans, garis tipis tanpa bayangan — tenang dan premium seperti buku yang dijilid rapi.'],
            'b' => ['Versi B · Asagi Terminal (Presisi)', 'Kanvas zinc bersih, teal asagi sebagai satu-satunya warna aksi dan caret merah shu sebagai aksen. Geist + Geist Mono dengan angka tabular, sudut rapat dan garis 1px — presisi seperti alat kerja tim engineering Tokyo.'],
        ], 'rules' => 'required|in:a,b'],
        'appearance.allow_user_theme' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => $yesNo, 'label' => 'Izinkan pengguna memilih tema A/B sendiri', 'help' => 'Bila nonaktif, semua pengguna melihat tema pilihan admin.', 'default' => false, 'rules' => 'boolean'],
        'appearance.logo_variant' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Gaya logo', 'default' => 'auto', 'options' => [
            'a' => ['Versi A · CF dalam hanko', 'Stempel merah hanko dengan huruf C dan F — tanda "disetujui" saat karya siswa diterima.'],
            'b' => ['Versi B · C1 caret', 'Huruf C dengan caret merah yang juga terbaca angka 1: C1 = "Coding First".'],
            'auto' => ['Ikuti tema', 'Logo menyesuaikan tema yang aktif (Versi A → hanko, Versi B → caret). Disarankan.'],
        ], 'rules' => 'required|in:a,b,auto'],
        'appearance.landing_version' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Template landing page', 'default' => 'a', 'options' => [
            'a' => ['Versi A · Majalah sekolah', 'Bab editorial bernomor di atas kertas washi, hero naskah dengan stempel, daftar isi program dan satu kutipan testimoni per bab.'],
            'b' => ['Versi B · Halaman produk', 'Hero jendela kode, strip angka dari data nyata, sorotan fitur produk, CTA bergaya terminal dan panel perlindungan data.'],
        ], 'rules' => 'required|in:a,b'],
        'appearance.dashboard_nav' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Navigasi dashboard (desktop)', 'default' => 'sidebar', 'options' => [
            'sidebar' => ['Sidebar', 'Menu samping berkelompok yang dapat diciutkan menjadi rail ikon — cocok untuk admin dengan banyak menu.'],
            'topnav' => ['Navigasi atas', 'Bilah atas dengan menu dropdown per kelompok — area konten lebih lebar.'],
        ], 'rules' => 'required|in:sidebar,topnav'],
        'appearance.mobile_nav' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Navigasi di ponsel', 'default' => 'tabs', 'options' => [
            'tabs' => ['Tab bawah', 'Lima tab utama per peran di bawah layar + lembar "Lainnya" — mudah dijangkau jempol.'],
            'drawer' => ['Menu laci', 'Tombol menu di bilah atas membuka seluruh menu dari samping.'],
        ], 'rules' => 'required|in:tabs,drawer'],
        'appearance.stat_style' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Gaya angka ringkasan (KPI)', 'default' => 'strip', 'options' => [
            'strip' => ['Strip tenang', 'Satu strip berpembatas garis tipis — kesan buku besar yang rapi.'],
            'cards' => ['Kartu terpisah', 'Setiap angka dalam kartu sendiri — lebih familiar.'],
        ], 'rules' => 'required|in:strip,cards'],
        'appearance.student_home' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Gaya beranda siswa', 'default' => 'tenang', 'options' => [
            'tenang' => ['Tenang', 'Struktur sama tenangnya dengan peran lain — cocok untuk remaja & dewasa.'],
            'ceria' => ['Ceria', 'Kartu sapaan, strip streak, cincin progres besar dan empty state yang merayakan — cocok untuk anak.'],
        ], 'rules' => 'required|in:tenang,ceria'],
        'appearance.auth_layout' => ['group' => 'appearance', 'section' => 'Versi A/B', 'type' => 'choice', 'label' => 'Tampilan halaman login & daftar', 'default' => 'split', 'options' => [
            'split' => ['Layar terbagi', 'Panel brand di kiri, formulir di kanan.'],
            'centered' => ['Kartu di tengah', 'Formulir ringkas di tengah layar dengan logo di atasnya.'],
        ], 'rules' => 'required|in:split,centered'],
        'appearance.density' => ['group' => 'appearance', 'section' => 'Kerapatan & mode warna', 'type' => 'select', 'label' => 'Kerapatan tampilan default', 'help' => 'Pengguna dapat mengubahnya di Preferensi. Perangkat sentuh selalu memakai area sentuh minimal 44px.', 'default' => 'comfortable', 'options' => ['comfortable' => 'Lega', 'compact' => 'Rapat'], 'rules' => 'required|in:comfortable,compact'],
        'appearance.color_mode' => ['group' => 'appearance', 'section' => 'Kerapatan & mode warna', 'type' => 'select', 'label' => 'Mode warna default', 'default' => 'system', 'options' => ['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Ikuti perangkat'], 'rules' => 'required|in:light,dark,system'],
        'appearance.allow_user_color_mode' => ['group' => 'appearance', 'section' => 'Kerapatan & mode warna', 'type' => $yesNo, 'label' => 'Izinkan pengguna memilih mode warna sendiri', 'default' => true, 'rules' => 'boolean'],
        'appearance.custom_logo' => ['group' => 'appearance', 'section' => 'Aset kustom', 'type' => 'image', 'label' => 'Logo kustom (opsional)', 'help' => 'Gunakan latar transparan. Mengganti logo bawaan bila diisi; SVG tidak diterima demi keamanan.', 'default' => null, 'rules' => $logoRules],
        'appearance.custom_logo_dark' => ['group' => 'appearance', 'section' => 'Aset kustom', 'type' => 'image', 'label' => 'Logo kustom untuk mode gelap', 'help' => 'Opsional. Dipakai saat mode gelap aktif; bila kosong, logo terang dipakai.', 'default' => null, 'rules' => $logoRules],

        // ── Landing page ──────────────────────────────────────────────────
        'landing.announcement' => ['group' => 'landing', 'section' => 'Pengumuman', 'type' => 'text', 'label' => 'Bilah pengumuman', 'help' => 'Kosongkan untuk menyembunyikan.', 'default' => '', 'rules' => 'nullable|string|max:160'],
        'landing.hero_title' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Judul utama', 'default' => 'Belajar coding dengan standar industri Jepang', 'rules' => 'required|string|max:120'],
        'landing.hero_subtitle' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'textarea', 'label' => 'Subjudul', 'default' => 'Kelas privat dan grup untuk anak, remaja dan dewasa — kurikulum terstruktur, mentor berpengalaman, dan laporan perkembangan yang transparan untuk orang tua.', 'rules' => 'nullable|string|max:300'],
        'landing.cta_primary_text' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tombol utama', 'default' => 'Daftar kelas percobaan', 'rules' => 'required|string|max:40'],
        'landing.cta_primary_url' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tautan tombol utama', 'help' => 'Awali dengan / (halaman situs ini), # (bagian halaman) atau https://.', 'default' => '/register', 'rules' => ['required', 'string', 'max:255', $safeLink]],
        'landing.cta_secondary_text' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tombol kedua', 'default' => 'Lihat program', 'rules' => 'nullable|string|max:40'],
        'landing.cta_secondary_url' => ['group' => 'landing', 'section' => 'Hero', 'type' => 'text', 'label' => 'Tautan tombol kedua', 'help' => 'Awali dengan / (halaman situs ini), # (bagian halaman) atau https://.', 'default' => '#program', 'rules' => ['nullable', 'string', 'max:255', $safeLink]],
        'landing.show_programs' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Program / kursus', 'default' => true, 'rules' => 'boolean'],
        'landing.show_teachers' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Pengajar', 'default' => true, 'rules' => 'boolean'],
        'landing.show_testimonials' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Testimoni orang tua', 'default' => true, 'rules' => 'boolean'],
        'landing.show_stats' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Angka pencapaian', 'default' => true, 'rules' => 'boolean'],
        'landing.show_faq' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Tanya jawab (FAQ)', 'default' => true, 'rules' => 'boolean'],
        'landing.show_top_students' => ['group' => 'landing', 'section' => 'Bagian yang ditampilkan', 'type' => $yesNo, 'label' => 'Siswa berprestasi', 'help' => 'Data anak termasuk data pribadi spesifik (UU PDP Pasal 4). Hanya siswa dengan persetujuan publikasi dari orang tua yang ditampilkan.', 'default' => false, 'rules' => 'boolean'],
        'landing.stats' => ['group' => 'landing', 'section' => 'Konten', 'type' => 'repeater', 'label' => 'Angka pencapaian', 'fields' => ['value' => 'Angka', 'label' => 'Keterangan'], 'item_rules' => ['value' => 'required|string|max:20', 'label' => 'required|string|max:60'], 'default' => [['value' => '1.200+', 'label' => 'Siswa belajar'], ['value' => '12', 'label' => 'Program'], ['value' => '15+', 'label' => 'Mentor'], ['value' => '4,9/5', 'label' => 'Kepuasan orang tua']], 'rules' => 'nullable|array|max:6'],
        'landing.faq' => ['group' => 'landing', 'section' => 'Konten', 'type' => 'repeater', 'label' => 'Tanya jawab', 'fields' => ['question' => 'Pertanyaan', 'answer' => ['label' => 'Jawaban', 'type' => 'textarea']], 'item_rules' => ['question' => 'required|string|max:200', 'answer' => 'required|string|max:1000'], 'default' => [
            ['question' => 'Usia berapa anak bisa mulai belajar?', 'answer' => 'Mulai usia 7 tahun dengan Scratch, lalu naik bertahap ke Python, web, dan game development.'],
            ['question' => 'Apakah kelas tersedia online?', 'answer' => 'Ya. Tersedia kelas online, privat ke rumah, dan kelas grup kecil.'],
            ['question' => 'Bagaimana orang tua memantau perkembangan?', 'answer' => 'Orang tua mendapat akun sendiri untuk melihat jadwal, laporan pertemuan, kehadiran, dan pembayaran.'],
            ['question' => 'Bagaimana data anak saya dilindungi?', 'answer' => 'Kami memproses data anak hanya dengan persetujuan orang tua/wali sesuai UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi. Lihat Kebijakan Privasi kami.'],
        ], 'rules' => 'nullable|array|max:20'],

        // ── Akademik ─────────────────────────────────────────────────────
        'academic.session_duration_minutes' => ['group' => 'academic', 'section' => 'Kelas & kehadiran', 'type' => 'number', 'label' => 'Durasi pertemuan default', 'unit' => 'menit', 'default' => 90, 'rules' => 'required|integer|min:15|max:480'],
        'academic.attendance_late_minutes' => ['group' => 'academic', 'section' => 'Kelas & kehadiran', 'type' => 'number', 'label' => 'Toleransi terlambat', 'unit' => 'menit', 'default' => 15, 'rules' => 'required|integer|min:0|max:120'],
        'academic.reports_visible_to_parents' => ['group' => 'academic', 'section' => 'Kelas & kehadiran', 'type' => $yesNo, 'label' => 'Laporan pertemuan terlihat oleh orang tua', 'default' => true, 'rules' => 'boolean'],
        'academic.certificate_prefix' => ['group' => 'academic', 'section' => 'Sertifikat', 'type' => 'text', 'label' => 'Prefiks nomor sertifikat', 'default' => 'CF', 'rules' => 'required|alpha_dash|max:10'],
        'academic.certificate_signer_name' => ['group' => 'academic', 'section' => 'Sertifikat', 'type' => 'text', 'label' => 'Nama penandatangan', 'default' => 'Direktur Akademik', 'rules' => 'nullable|string|max:80'],
        'academic.certificate_signer_title' => ['group' => 'academic', 'section' => 'Sertifikat', 'type' => 'text', 'label' => 'Jabatan penandatangan', 'default' => 'Coding First', 'rules' => 'nullable|string|max:80'],

        // ── Keuangan ─────────────────────────────────────────────────────
        'finance.currency' => ['group' => 'finance', 'section' => 'Tagihan', 'type' => 'select', 'label' => 'Mata uang', 'default' => 'IDR', 'options' => ['IDR' => 'Rupiah (IDR)'], 'rules' => 'required|in:IDR'],
        'finance.invoice_prefix' => ['group' => 'finance', 'section' => 'Tagihan', 'type' => 'text', 'label' => 'Prefiks nomor tagihan', 'default' => 'INV', 'rules' => 'required|alpha_dash|max:10'],
        'finance.payment_due_days' => ['group' => 'finance', 'section' => 'Tagihan', 'type' => 'number', 'label' => 'Jatuh tempo', 'unit' => 'hari', 'default' => 7, 'rules' => 'required|integer|min:0|max:90'],
        'finance.tax_percent' => ['group' => 'finance', 'section' => 'Tagihan', 'type' => 'number', 'label' => 'Pajak', 'unit' => '%', 'default' => 0, 'rules' => 'required|numeric|min:0|max:100'],
        'finance.bank_accounts' => ['group' => 'finance', 'section' => 'Pembayaran', 'type' => 'repeater', 'label' => 'Rekening pembayaran', 'fields' => ['bank' => 'Bank', 'number' => 'No. rekening', 'holder' => 'Atas nama'], 'item_rules' => ['bank' => 'required|string|max:60', 'number' => ['required', 'regex:/^[0-9][0-9 .-]{3,29}$/'], 'holder' => 'required|string|max:120'], 'default' => [], 'rules' => 'nullable|array|max:5'],
        'finance.payment_instructions' => ['group' => 'finance', 'section' => 'Pembayaran', 'type' => 'textarea', 'label' => 'Instruksi pembayaran', 'default' => 'Transfer sesuai nominal tagihan lalu unggah/kirim bukti pembayaran ke admin.', 'rules' => 'nullable|string|max:1000'],

        // ── Gamifikasi ───────────────────────────────────────────────────
        'gamification.enabled' => ['group' => 'gamification', 'section' => 'Poin', 'type' => $yesNo, 'label' => 'Aktifkan gamifikasi', 'default' => true, 'rules' => 'boolean'],
        'gamification.points_login' => ['group' => 'gamification', 'section' => 'Poin', 'type' => 'number', 'label' => 'Poin login harian', 'default' => 5, 'rules' => 'required|integer|min:0|max:1000'],
        'gamification.points_attendance' => ['group' => 'gamification', 'section' => 'Poin', 'type' => 'number', 'label' => 'Poin kehadiran', 'default' => 10, 'rules' => 'required|integer|min:0|max:1000'],
        'gamification.points_assignment' => ['group' => 'gamification', 'section' => 'Poin', 'type' => 'number', 'label' => 'Poin tugas selesai', 'default' => 20, 'rules' => 'required|integer|min:0|max:1000'],
        'gamification.leaderboard_visibility' => ['group' => 'gamification', 'section' => 'Papan peringkat', 'type' => 'select', 'label' => 'Papan peringkat terlihat oleh', 'default' => 'members', 'options' => ['off' => 'Nonaktif', 'class' => 'Teman sekelas saja', 'members' => 'Semua pengguna yang login'], 'rules' => 'required|in:off,class,members'],
        'gamification.leaderboard_name_display' => ['group' => 'gamification', 'section' => 'Papan peringkat', 'type' => 'select', 'label' => 'Nama siswa di papan peringkat', 'help' => 'Untuk melindungi data anak, gunakan nama depan atau inisial.', 'default' => 'first_name', 'options' => ['first_name' => 'Nama depan', 'initials' => 'Inisial', 'full' => 'Nama lengkap'], 'rules' => 'required|in:first_name,initials,full'],

        // ── Notifikasi & Email ───────────────────────────────────────────
        'notifications.from_name' => ['group' => 'notifications', 'section' => 'Pengirim email', 'type' => 'text', 'label' => 'Nama pengirim email', 'default' => 'Coding First', 'rules' => 'required|string|max:80'],
        'notifications.from_address' => ['group' => 'notifications', 'section' => 'Pengirim email', 'type' => 'email', 'label' => 'Alamat pengirim email', 'default' => 'no-reply@codingfirst.id', 'rules' => 'required|email|max:120'],
        'notifications.admin_email' => ['group' => 'notifications', 'section' => 'Pengirim email', 'type' => 'email', 'label' => 'Email admin penerima notifikasi', 'default' => 'admin@codingfirst.id', 'rules' => 'required|email|max:120'],
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
        'security.session_lifetime_minutes' => ['group' => 'security', 'section' => 'Sesi & login', 'type' => 'number', 'label' => 'Durasi sesi', 'unit' => 'menit', 'help' => 'Pengguna yang tidak aktif selama ini harus masuk kembali.', 'default' => 120, 'rules' => 'required|integer|min:15|max:10080'],
        'security.security_headers' => ['group' => 'security', 'section' => 'Sesi & login', 'type' => $yesNo, 'label' => 'Kirim header keamanan HTTP', 'default' => true, 'rules' => 'boolean'],

        // ── Privasi & PDP (UU No. 27 Tahun 2022) ─────────────────────────
        'privacy.controller_name' => ['group' => 'privacy', 'section' => 'Pengendali data', 'type' => 'text', 'label' => 'Nama Pengendali Data Pribadi', 'default' => 'Coding First Indonesia', 'rules' => 'required|string|max:120'],
        'privacy.controller_address' => ['group' => 'privacy', 'section' => 'Pengendali data', 'type' => 'textarea', 'label' => 'Alamat Pengendali', 'default' => 'Jakarta, Indonesia', 'rules' => 'required|string|max:300'],
        'privacy.dpo_name' => ['group' => 'privacy', 'section' => 'Pejabat Pelindungan Data Pribadi (DPO)', 'type' => 'text', 'label' => 'Nama pejabat/petugas PDP', 'default' => 'Petugas Pelindungan Data Pribadi', 'rules' => 'required|string|max:120'],
        'privacy.dpo_email' => ['group' => 'privacy', 'section' => 'Pejabat Pelindungan Data Pribadi (DPO)', 'type' => 'email', 'label' => 'Email DPO', 'default' => 'privasi@codingfirst.id', 'rules' => 'required|email|max:120'],
        'privacy.dpo_phone' => ['group' => 'privacy', 'section' => 'Pejabat Pelindungan Data Pribadi (DPO)', 'type' => 'tel', 'label' => 'Telepon DPO', 'default' => '', 'rules' => 'nullable|string|max:30'],
        'privacy.cookie_banner_enabled' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => $yesNo, 'label' => 'Tampilkan banner persetujuan cookie', 'default' => true, 'rules' => 'boolean'],
        'privacy.cookie_banner_text' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => 'textarea', 'label' => 'Teks banner cookie', 'default' => 'Kami memakai cookie yang diperlukan agar situs berfungsi. Dengan persetujuan Anda, kami juga memakai cookie opsional untuk preferensi dan analitik. Anda dapat mengubah pilihan kapan saja.', 'rules' => 'required|string|max:500'],
        'privacy.child_age_threshold' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => 'number', 'label' => 'Batas usia anak', 'unit' => 'tahun', 'help' => 'Di bawah usia ini, pemrosesan data memerlukan persetujuan orang tua/wali (UU PDP Pasal 25).', 'default' => 18, 'rules' => 'required|integer|min:13|max:21'],
        'privacy.require_parental_consent' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => $yesNo, 'label' => 'Wajib persetujuan orang tua/wali untuk anak', 'default' => true, 'rules' => 'boolean'],
        'privacy.policy_reacceptance_required' => ['group' => 'privacy', 'section' => 'Persetujuan', 'type' => $yesNo, 'label' => 'Minta persetujuan ulang saat kebijakan berubah', 'default' => true, 'rules' => 'boolean'],
        'privacy.dsr_sla_hours' => ['group' => 'privacy', 'section' => 'Tenggat', 'type' => 'number', 'label' => 'Tenggat respons permintaan subjek data', 'unit' => 'jam', 'help' => 'UU PDP umumnya menetapkan paling lambat 3 x 24 jam.', 'default' => 72, 'rules' => 'required|integer|min:24|max:720'],
        'privacy.breach_notification_hours' => ['group' => 'privacy', 'section' => 'Tenggat', 'type' => 'number', 'label' => 'Tenggat notifikasi kegagalan pelindungan data', 'unit' => 'jam', 'help' => 'UU PDP Pasal 46: paling lambat 3 x 24 jam.', 'default' => 72, 'rules' => 'required|integer|min:24|max:72'],
        'privacy.retention_inactive_account_months' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Anonimkan akun tidak aktif setelah', 'unit' => 'bulan', 'default' => 24, 'rules' => 'required|integer|min:6|max:120'],
        'privacy.retention_audit_log_days' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Simpan log audit selama', 'unit' => 'hari', 'default' => 365, 'rules' => 'required|integer|min:90|max:3650'],
        'privacy.retention_financial_years' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Simpan catatan keuangan selama', 'unit' => 'tahun', 'help' => 'Kewajiban penyimpanan dokumen keuangan/perpajakan umumnya 10 tahun.', 'default' => 10, 'rules' => 'required|integer|min:5|max:30'],
        'privacy.retention_chat_months' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Simpan pesan chat selama', 'unit' => 'bulan', 'default' => 24, 'rules' => 'required|integer|min:1|max:120'],
        'privacy.retention_export_days' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'number', 'label' => 'Hapus berkas ekspor data setelah', 'unit' => 'hari', 'default' => 7, 'rules' => 'required|integer|min:1|max:30'],
        'privacy.erasure_mode' => ['group' => 'privacy', 'section' => 'Retensi', 'type' => 'select', 'label' => 'Cara penghapusan akun', 'default' => 'anonymize', 'options' => ['anonymize' => 'Anonimkan (catatan keuangan tetap tersimpan sesuai kewajiban hukum)', 'delete' => 'Hapus permanen bila tidak ada kewajiban retensi'], 'rules' => 'required|in:anonymize,delete'],
        'privacy.show_student_names_public' => ['group' => 'privacy', 'section' => 'Publikasi', 'type' => $yesNo, 'label' => 'Tampilkan nama siswa di halaman publik', 'help' => 'Tetap hanya untuk siswa yang orang tuanya memberi persetujuan publikasi.', 'default' => false, 'rules' => 'boolean'],

        // ── PWA ──────────────────────────────────────────────────────────
        'pwa.enabled' => ['group' => 'pwa', 'section' => 'Pemasangan', 'type' => $yesNo, 'label' => 'Aktifkan aplikasi yang dapat dipasang (PWA)', 'default' => true, 'rules' => 'boolean'],
        'pwa.name' => ['group' => 'pwa', 'section' => 'Identitas aplikasi', 'type' => 'text', 'label' => 'Nama aplikasi', 'default' => 'Coding First', 'rules' => 'required|string|max:45'],
        'pwa.short_name' => ['group' => 'pwa', 'section' => 'Identitas aplikasi', 'type' => 'text', 'label' => 'Nama pendek (di bawah ikon)', 'default' => 'CoFirst', 'rules' => 'required|string|max:12'],
        'pwa.description' => ['group' => 'pwa', 'section' => 'Identitas aplikasi', 'type' => 'text', 'label' => 'Deskripsi', 'default' => 'Portal belajar Coding First untuk siswa, orang tua dan pengajar.', 'rules' => 'nullable|string|max:160'],
        'pwa.background_color' => ['group' => 'pwa', 'section' => 'Tampilan aplikasi', 'type' => 'color', 'label' => 'Warna latar splash screen', 'default' => '#ffffff', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/'],
        'pwa.display' => ['group' => 'pwa', 'section' => 'Tampilan aplikasi', 'type' => 'select', 'label' => 'Mode tampilan', 'default' => 'standalone', 'options' => ['standalone' => 'Standalone (seperti aplikasi)', 'minimal-ui' => 'Minimal UI', 'browser' => 'Browser biasa'], 'rules' => 'required|in:standalone,minimal-ui,browser'],
        'pwa.install_prompt' => ['group' => 'pwa', 'section' => 'Pemasangan', 'type' => $yesNo, 'label' => 'Tampilkan ajakan pasang aplikasi', 'default' => true, 'rules' => 'boolean'],

        // ── Integrasi ────────────────────────────────────────────────────
        'integrations.whatsapp_button' => ['group' => 'integrations', 'section' => 'Situs publik', 'type' => $yesNo, 'label' => 'Tombol WhatsApp mengambang di situs publik', 'default' => false, 'rules' => 'boolean'],
        'integrations.maps_embed' => ['group' => 'integrations', 'section' => 'Situs publik', 'type' => $yesNo, 'label' => 'Sematkan peta Google Maps (memuat setelah persetujuan cookie)', 'default' => false, 'rules' => 'boolean'],
        'integrations.analytics_measurement_id' => ['group' => 'integrations', 'section' => 'Analitik', 'type' => 'text', 'label' => 'ID analitik (opsional)', 'help' => 'Format G-XXXXXXX. Skrip analitik hanya dimuat setelah pengunjung menyetujui kategori Analitik.', 'placeholder' => 'G-XXXXXXXXXX', 'default' => '', 'rules' => ['nullable', 'string', 'max:40', 'regex:/^G-[A-Z0-9]{4,20}$/']],
        'integrations.default_meeting_platform' => ['group' => 'integrations', 'section' => 'Kelas online', 'type' => 'select', 'label' => 'Platform kelas online default', 'default' => 'google_meet', 'options' => ['google_meet' => 'Google Meet', 'zoom' => 'Zoom', 'teams' => 'Microsoft Teams'], 'rules' => 'required|in:google_meet,zoom,teams'],

        // ── Sistem ───────────────────────────────────────────────────────
        'system.dashboard_announcement' => ['group' => 'system', 'section' => 'Pemberitahuan', 'type' => 'text', 'label' => 'Pengumuman di semua dashboard', 'help' => 'Kosongkan untuk menyembunyikan.', 'default' => '', 'rules' => 'nullable|string|max:200'],
        'system.maintenance_notice' => ['group' => 'system', 'section' => 'Pemberitahuan', 'type' => 'text', 'label' => 'Pemberitahuan pemeliharaan terjadwal', 'default' => '', 'rules' => 'nullable|string|max:200'],
    ],
];
