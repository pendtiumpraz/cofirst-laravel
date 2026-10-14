<?php

namespace App\Support\Navigation;

/**
 * Raw menu definitions per role (Indonesian labels, heroicon names, route names).
 *
 * Items are filtered by Navigation::for(): an item only appears when its route
 * exists and the user passes the route's role middleware, so entries for modules
 * that are still being built can be listed here safely.
 *
 * Item keys: label, route, icon (heroicon, outline set), active (routeIs patterns),
 * badge ('chat'|null), roles (optional extra role gate), children (optional items).
 */
final class Menus
{
    /**
     * Grouped sections for a role (without the shared "Akun" section).
     *
     * @return array<int, array{key:string, label:?string, items:array<int, array<string, mixed>>}>
     */
    public static function forRole(string $role): array
    {
        return match ($role) {
            'superadmin', 'admin' => self::admin(),
            'teacher' => self::teacher(),
            'student' => self::student(),
            'parent' => self::parent(),
            'finance' => self::finance(),
            default => [],
        };
    }

    /**
     * Personal items every user sees (sidebar footer, user menu, "Lainnya" sheet).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function account(): array
    {
        return [
            self::item('Profil', 'profile.edit', 'user-circle', ['profile.*']),
            self::item('Preferensi', 'account.preferences', 'adjustments-horizontal', ['account.preferences*']),
            self::item('Privasi & Data Saya', 'account.privacy.index', 'finger-print', ['account.privacy.*']),
        ];
    }

    /**
     * Bottom-tab candidates per role: [label, [route candidates…], icon]. Shared spec §11.
     *
     * @return array<int, array{0:string, 1:array<int, string>, 2:string}>
     */
    public static function tabs(string $role): array
    {
        return match ($role) {
            'student' => [
                ['Beranda', ['student.dashboard'], 'home'],
                ['Kelas', ['student.classes'], 'rectangle-group'],
                ['Jadwal', ['student.schedules.index'], 'calendar-days'],
                ['Pencapaian', ['gamification.index', 'gamification.badges'], 'trophy'],
            ],
            'parent' => [
                ['Beranda', ['parent.dashboard'], 'home'],
                ['Anak', ['parent.children.index'], 'user-group'],
                ['Laporan', ['parent.reports.index'], 'document-chart-bar'],
                ['Tagihan', ['parent.invoices.index', 'parent.payments.index', 'parent.progress.index'], 'receipt-percent'],
            ],
            'teacher' => [
                ['Beranda', ['teacher.dashboard'], 'home'],
                ['Kelas', ['teacher.classes'], 'rectangle-group'],
                ['Laporan', ['class-reports.index', 'teacher.reports.index'], 'clipboard-document-list'],
                ['Siswa', ['teacher.students.index', 'teacher.student-progress.index'], 'user-group'],
            ],
            'finance' => [
                ['Beranda', ['finance.dashboard'], 'home'],
                ['Transaksi', ['finance.transactions.index'], 'banknotes'],
                ['Tagihan', ['finance.invoices.index', 'finance.reports.daily'], 'receipt-percent'],
                ['Laporan', ['finance.reports.index'], 'document-chart-bar'],
            ],
            'superadmin', 'admin' => [
                ['Beranda', ['admin.dashboard'], 'home'],
                ['Pengguna', ['admin.users.index'], 'users'],
                ['Kelas', ['admin.classes.index'], 'rectangle-group'],
                ['Keuangan', ['admin.finance.dashboard', 'finance.transactions.index'], 'banknotes'],
            ],
            default => [],
        };
    }

    /**
     * Admin / superadmin information architecture (spec: desktop_nav ADMIN IA).
     */
    private static function admin(): array
    {
        return [
            self::section('utama', null, [
                self::item('Beranda', 'admin.dashboard', 'home', ['admin.dashboard', 'dashboard']),
                self::item('Pengguna', 'admin.users.index', 'users', ['admin.users.*']),
            ]),
            self::section('akademik', 'Akademik', [
                self::item('Kursus', 'admin.courses.index', 'book-open', ['admin.courses.*']),
                self::item('Kelas', 'admin.classes.index', 'rectangle-group', ['admin.classes.*']),
                self::item('Pendaftaran', 'admin.enrollments.index', 'clipboard-document-check', ['admin.enrollments.*']),
                self::item('Jadwal', 'admin.schedules.index', 'calendar-days', ['admin.schedules.*']),
                self::item('Kurikulum', 'admin.curriculums.index', 'queue-list', ['admin.curriculums.*']),
                self::item('Silabus', 'admin.syllabuses.index', 'document-text', ['admin.syllabuses.*']),
                self::item('Materi', 'admin.materials.index', 'folder-open', ['admin.materials.*']),
            ]),
            self::section('aktivitas', 'Aktivitas Kelas', [
                self::item('Berita Acara', 'class-reports.index', 'clipboard-document-list', ['class-reports.*']),
                self::item('Laporan Siswa', 'admin.reports.index', 'document-chart-bar', ['admin.reports.*']),
                self::item('Foto Kelas', 'admin.class-photos.index', 'camera', ['admin.class-photos.*']),
                self::item('Galeri Proyek', 'project-gallery.index', 'photo', ['project-gallery.*']),
            ]),
            self::section('sertifikat', 'Sertifikat & Gamifikasi', [
                self::item('Templat Sertifikat', 'admin.certificate-templates.index', 'document-duplicate', ['admin.certificate-templates.*']),
                self::item('Sertifikat', 'certificates.index', 'academic-cap', ['certificates.*']),
                self::item('Buat Massal', 'admin.certificates.bulk-generate', 'sparkles', ['admin.certificates.bulk-generate*']),
                self::item('Lencana', 'admin.badges.index', 'star', ['admin.badges.*']),
                self::item('Hadiah', 'admin.rewards.index', 'gift', ['admin.rewards.index', 'admin.rewards.create', 'admin.rewards.show', 'admin.rewards.edit']),
                self::item('Penukaran Hadiah', 'admin.rewards.redemptions', 'arrows-right-left', ['admin.rewards.redemptions*']),
                self::item('Papan Peringkat', 'gamification.leaderboard', 'trophy', ['gamification.leaderboard']),
            ]),
            self::section('keuangan', 'Keuangan', [
                self::item('Ringkasan Keuangan', 'admin.finance.dashboard', 'chart-pie', ['admin.finance.dashboard']),
                self::item('Analitik Keuangan', 'admin.finance.reports', 'presentation-chart-line', ['admin.finance.reports']),
                self::item('Transaksi', 'finance.transactions.index', 'banknotes', ['finance.transactions.*']),
                self::item('Tagihan', 'finance.invoices.index', 'receipt-percent', ['finance.invoices.*']),
                self::item('Laporan Keuangan', 'finance.reports.index', 'document-chart-bar', ['finance.reports.index']),
                self::item('Laporan Harian', 'finance.reports.daily', 'calendar', ['finance.reports.daily']),
            ]),
            self::section('konten', 'Konten Situs', [
                self::item('Testimoni', 'admin.testimonials.index', 'chat-bubble-bottom-center-text', ['admin.testimonials.*']),
            ]),
            self::section('privasi', 'Privasi & PDP', PrivacyMenu::items()),
            self::section('sistem', 'Sistem', [
                self::item('Pengaturan', 'admin.settings.index', 'cog-6-tooth', ['admin.settings.*']),
                self::item('Peran', 'admin.roles.index', 'key', ['admin.roles.*'], ['superadmin']),
                self::item('Izin Akses', 'admin.permissions.index', 'lock-closed', ['admin.permissions.*'], ['superadmin']),
                self::item('UI Kit', 'admin.ui-kit', 'swatch', ['admin.ui-kit*'], ['superadmin']),
            ]),
            self::section('komunikasi', 'Komunikasi', [self::chat()]),
        ];
    }

    private static function teacher(): array
    {
        return [
            self::section('utama', null, [
                self::item('Beranda', 'teacher.dashboard', 'home', ['teacher.dashboard', 'dashboard']),
            ]),
            self::section('mengajar', 'Mengajar', [
                self::item('Kelas Saya', 'teacher.classes', 'rectangle-group', ['teacher.classes', 'teacher.class-detail*']),
                self::item('Siswa', 'teacher.students.index', 'user-group', ['teacher.students.*']),
                self::item('Absensi', 'teacher.attendance.index', 'clipboard-document-check', ['teacher.attendance.*']),
                self::item('Berita Acara', 'class-reports.index', 'clipboard-document-list', ['class-reports.*']),
                self::item('Laporan Siswa', 'teacher.reports.index', 'document-chart-bar', ['teacher.reports.*']),
                self::item('Progres Siswa', 'teacher.student-progress.index', 'chart-bar', ['teacher.student-progress.*']),
                self::item('Serah Terima', 'teacher.handovers.index', 'arrows-right-left', ['teacher.handovers.*']),
            ]),
            self::section('materi', 'Materi Ajar', [
                self::item('Kurikulum', 'teacher.curriculums.index', 'queue-list', ['teacher.curriculums.*']),
                self::item('Silabus', 'teacher.syllabuses.index', 'document-text', ['teacher.syllabuses.*']),
                self::item('Materi', 'teacher.materials.index', 'folder-open', ['teacher.materials.*']),
            ]),
            self::section('galeri', 'Galeri & Sertifikat', [
                self::item('Foto Kelas', 'teacher.class-photos.index', 'camera', ['teacher.class-photos.*']),
                self::item('Galeri Proyek', 'project-gallery.index', 'photo', ['project-gallery.*']),
                self::item('Buat Sertifikat', 'teacher.certificates.generate', 'sparkles', ['teacher.certificates.*']),
                self::item('Sertifikat Terbit', 'certificates.index', 'academic-cap', ['certificates.*']),
                self::item('Papan Peringkat', 'gamification.leaderboard', 'trophy', ['gamification.leaderboard']),
            ]),
            self::section('komunikasi', 'Komunikasi', [self::chat()]),
        ];
    }

    private static function student(): array
    {
        return [
            self::section('utama', null, [
                self::item('Beranda', 'student.dashboard', 'home', ['student.dashboard', 'dashboard']),
            ]),
            self::section('belajar', 'Belajar', [
                self::item('Kelas Saya', 'student.classes', 'rectangle-group', ['student.classes*']),
                self::item('Jadwal', 'student.schedules.index', 'calendar-days', ['student.schedules.*']),
                self::item('Materi', 'student.materials.index', 'folder-open', ['student.materials.*', 'student.progress.*']),
                self::item('Kurikulum', 'student.curriculum.index', 'queue-list', ['student.curriculum.*']),
                self::item('Laporan Belajar', 'student.reports.index', 'document-chart-bar', ['student.reports.*']),
                self::item('Katalog Kursus', 'student.courses.index', 'book-open', ['student.courses.*']),
            ]),
            self::section('prestasi', 'Karya & Prestasi', [
                self::item('Proyek Saya', 'project-gallery.index', 'photo', ['project-gallery.index', 'project-gallery.create', 'project-gallery.show', 'project-gallery.edit']),
                self::item('Galeri Unggulan', 'project-gallery.featured', 'sparkles', ['project-gallery.featured']),
                self::item('Sertifikat', 'certificates.index', 'academic-cap', ['certificates.*']),
                self::item('Poin Saya', 'gamification.index', 'bolt', ['gamification.index']),
                self::item('Riwayat Poin', 'gamification.point-history', 'clock', ['gamification.point-history']),
                self::item('Lencana', 'gamification.badges', 'star', ['gamification.badges*']),
                self::item('Toko Hadiah', 'gamification.rewards', 'gift', ['gamification.rewards*']),
                self::item('Penukaran Saya', 'gamification.redemptions', 'ticket', ['gamification.redemptions']),
                self::item('Papan Peringkat', 'gamification.leaderboard', 'trophy', ['gamification.leaderboard']),
            ]),
            self::section('administrasi', 'Administrasi', [
                self::item('Pembayaran', 'student.payments', 'credit-card', ['student.payments*']),
                self::chat(),
            ]),
        ];
    }

    private static function parent(): array
    {
        return [
            self::section('utama', null, [
                self::item('Beranda', 'parent.dashboard', 'home', ['parent.dashboard', 'dashboard']),
            ]),
            self::section('keluarga', 'Keluarga', [
                self::item('Anak Saya', 'parent.children.index', 'user-group', ['parent.children.*', 'parent.child-schedule', 'parent.child-payments']),
                self::item('Progres Anak', 'parent.progress.index', 'chart-bar', ['parent.progress.*']),
                self::item('Kurikulum', 'parent.curriculum.index', 'queue-list', ['parent.curriculum.*']),
                self::item('Laporan', 'parent.reports.index', 'document-chart-bar', ['parent.reports.*', 'parent.child-reports']),
                self::item('Tagihan', 'parent.invoices.index', 'receipt-percent', ['parent.invoices.*']),
            ]),
            self::section('galeri', 'Galeri & Prestasi', [
                self::item('Foto Kelas', 'parent.class-photos.index', 'camera', ['parent.class-photos.*']),
                self::item('Proyek Anak', 'project-gallery.index', 'photo', ['project-gallery.*']),
                self::item('Sertifikat Anak', 'certificates.index', 'academic-cap', ['certificates.*']),
                self::item('Lencana', 'gamification.badges', 'star', ['gamification.badges*']),
                self::item('Toko Hadiah', 'gamification.rewards', 'gift', ['gamification.rewards*']),
            ]),
            self::section('komunikasi', 'Komunikasi', [
                self::chat(),
                self::item('Hubungi Admin', 'parent.contact-admin', 'envelope', ['parent.contact-admin*']),
                self::item('Kirim Testimoni', 'parent.testimonials.create', 'chat-bubble-bottom-center-text', ['parent.testimonials.*']),
            ]),
        ];
    }

    private static function finance(): array
    {
        return [
            self::section('utama', null, [
                self::item('Beranda', 'finance.dashboard', 'home', ['finance.dashboard', 'dashboard']),
            ]),
            self::section('keuangan', 'Keuangan', [
                self::item('Transaksi', 'finance.transactions.index', 'banknotes', ['finance.transactions.*']),
                self::item('Tagihan', 'finance.invoices.index', 'receipt-percent', ['finance.invoices.*']),
                self::item('Laporan', 'finance.reports.index', 'document-chart-bar', ['finance.reports.index']),
                self::item('Laporan Harian', 'finance.reports.daily', 'calendar', ['finance.reports.daily']),
            ]),
            self::section('komunikasi', 'Komunikasi', [self::chat()]),
        ];
    }

    private static function chat(): array
    {
        return self::item('Pesan', 'chat.index', 'chat-bubble-left-right', ['chat.*'], null, 'chat');
    }

    /**
     * Build a section definition.
     */
    public static function section(string $key, ?string $label, array $items): array
    {
        return ['key' => $key, 'label' => $label, 'items' => $items];
    }

    /**
     * Build an item definition.
     *
     * @param  array<int, string>  $active  routeIs() patterns
     * @param  array<int, string>|null  $roles  extra role gate (e.g. superadmin-only)
     */
    public static function item(string $label, string $route, string $icon, array $active = [], ?array $roles = null, ?string $badge = null): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
            'active' => $active ?: [$route],
            'roles' => $roles,
            'badge' => $badge,
            'children' => [],
        ];
    }
}
