<?php

namespace App\Services\Academic;

/**
 * Typed access to the academic settings, with safe defaults.
 *
 * The values are read through setting() so an admin can change them in
 * Admin › Pengaturan › Akademik. Keys that are not (yet) in config/settings.php
 * fall back to the defaults below, so the academic module works either way.
 *
 * Two-option product decisions (A/B):
 *   - academic.material_unlock_mode: 'after_meeting' (materials open once the meeting
 *     is completed) | 'upcoming' (also the next meeting, so students can prepare) |
 *     'all' (every active material of the curriculum is open).
 *   - academic.class_report_visibility: 'own' (a teacher sees only reports they wrote)
 *     | 'class' (every teacher of the class can read them; editing stays owner-only).
 *   - academic.admin_can_edit_class_reports: false (admins read and delete) | true
 *     (admins may correct a report; every correction is audit-logged).
 *   - academic.material_tracking_mode: 'detailed' (one row per visit, throttled) |
 *     'minimal' (one row per student and material: only "opened" and "last opened").
 */
class AcademicSettings
{
    public const UNLOCK_AFTER_MEETING = 'after_meeting';

    public const UNLOCK_UPCOMING = 'upcoming';

    public const UNLOCK_ALL = 'all';

    public const TRACKING_DETAILED = 'detailed';

    public const TRACKING_MINIMAL = 'minimal';

    /**
     * Registry entries the settings owner can add to config/settings.php so these
     * options appear in Admin › Pengaturan › Akademik.
     *
     * Returns:
     *     array<string, array<string, mixed>>: key => field definition.
     */
    public static function definitions(): array
    {
        return [
            'academic.material_unlock_mode' => ['group' => 'academic', 'section' => 'Materi', 'type' => 'choice', 'label' => 'Kapan materi terbuka untuk siswa', 'default' => self::UNLOCK_AFTER_MEETING, 'options' => [
                self::UNLOCK_AFTER_MEETING => ['Setelah pertemuan selesai', 'Materi sebuah pertemuan terbuka setelah pengajar melaporkan pertemuan itu.'],
                self::UNLOCK_UPCOMING => ['Satu pertemuan lebih awal', 'Materi pertemuan berikutnya juga terbuka agar siswa bisa bersiap.'],
                self::UNLOCK_ALL => ['Semua terbuka', 'Seluruh materi aktif kurikulum langsung terbuka bagi siswa yang terdaftar.'],
            ], 'rules' => 'required|in:after_meeting,upcoming,all'],
            'academic.material_tracking_mode' => ['group' => 'academic', 'section' => 'Materi', 'type' => 'choice', 'label' => 'Pencatatan akses materi', 'default' => self::TRACKING_DETAILED, 'options' => [
                self::TRACKING_DETAILED => ['Rinci', 'Setiap kunjungan dicatat (maks. sekali per 30 menit) untuk melihat keterlibatan belajar.'],
                self::TRACKING_MINIMAL => ['Minimal', 'Hanya dicatat bahwa materi pernah dibuka dan kapan terakhir dibuka (minimisasi data).'],
            ], 'rules' => 'required|in:detailed,minimal'],
            'academic.material_max_upload_mb' => ['group' => 'academic', 'section' => 'Materi', 'type' => 'number', 'label' => 'Ukuran maksimum berkas materi (MB)', 'help' => 'Sesuaikan dengan upload_max_filesize server. Untuk video, gunakan tautan YouTube/Drive.', 'default' => 20, 'rules' => 'required|integer|min:1|max:200'],
            'academic.retention_material_access_days' => ['group' => 'academic', 'section' => 'Materi', 'type' => 'number', 'label' => 'Simpan riwayat akses materi selama (hari)', 'default' => 365, 'rules' => 'required|integer|min:30|max:1825'],
            'academic.class_report_edit_days' => ['group' => 'academic', 'section' => 'Berita acara', 'type' => 'number', 'label' => 'Batas ubah berita acara (hari setelah pertemuan)', 'default' => 7, 'rules' => 'required|integer|min:0|max:60'],
            'academic.class_report_visibility' => ['group' => 'academic', 'section' => 'Berita acara', 'type' => 'choice', 'label' => 'Berita acara terlihat oleh pengajar', 'default' => 'own', 'options' => [
                'own' => ['Penulisnya saja', 'Pengajar hanya melihat berita acara yang ia tulis.'],
                'class' => ['Semua pengajar kelas', 'Pengajar pengganti/pendamping dapat membaca catatan pertemuan sebelumnya; mengubah tetap hanya penulis.'],
            ], 'rules' => 'required|in:own,class'],
            'academic.admin_can_edit_class_reports' => ['group' => 'academic', 'section' => 'Berita acara', 'type' => 'boolean', 'label' => 'Admin dapat mengoreksi berita acara', 'help' => 'Setiap koreksi dicatat di log audit.', 'default' => false, 'rules' => 'boolean'],
            'academic.media_link_hosts' => ['group' => 'academic', 'section' => 'Berita acara', 'type' => 'text', 'label' => 'Domain tautan dokumentasi yang diizinkan', 'help' => 'Pisahkan dengan koma. Foto/video anak hanya boleh disimpan di penyimpanan milik lembaga.', 'default' => 'drive.google.com,docs.google.com', 'rules' => 'required|string|max:300'],
        ];
    }

    /**
     * Read one academic setting with the module default.
     *
     * Args:
     *     key (string): Full key, e.g. "academic.material_unlock_mode".
     *
     * Returns:
     *     mixed: Stored value or default.
     */
    public static function get(string $key): mixed
    {
        $default = self::definitions()[$key]['default'] ?? null;

        try {
            return setting($key, $default) ?? $default;
        } catch (\Throwable) {
            // Reason: settings table missing (fresh install) must not break the portals.
            return $default;
        }
    }

    /** Material unlock mode (after_meeting | upcoming | all). */
    public static function unlockMode(): string
    {
        $mode = (string) self::get('academic.material_unlock_mode');

        return in_array($mode, [self::UNLOCK_AFTER_MEETING, self::UNLOCK_UPCOMING, self::UNLOCK_ALL], true)
            ? $mode : self::UNLOCK_AFTER_MEETING;
    }

    /** Material access tracking mode (detailed | minimal). */
    public static function trackingMode(): string
    {
        return self::get('academic.material_tracking_mode') === self::TRACKING_MINIMAL
            ? self::TRACKING_MINIMAL : self::TRACKING_DETAILED;
    }

    /** Maximum upload size of a material file in kilobytes. */
    public static function maxUploadKb(): int
    {
        return max(1, (int) self::get('academic.material_max_upload_mb')) * 1024;
    }

    /** Number of days after the meeting during which a teacher may edit a class report. */
    public static function classReportEditDays(): int
    {
        return max(0, (int) self::get('academic.class_report_edit_days'));
    }

    /** Whether every teacher of a class may read its class reports. */
    public static function classReportsSharedWithClassTeachers(): bool
    {
        return self::get('academic.class_report_visibility') === 'class';
    }

    /** Whether admins may correct class reports. */
    public static function adminCanEditClassReports(): bool
    {
        return filter_var(self::get('academic.admin_can_edit_class_reports'), FILTER_VALIDATE_BOOLEAN);
    }

    /** Whether parents may read their children's meeting and progress reports. */
    public static function reportsVisibleToParents(): bool
    {
        try {
            return filter_var(setting('academic.reports_visible_to_parents', true), FILTER_VALIDATE_BOOLEAN);
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Hosts allowed for the "learning media" link of a class report.
     *
     * Returns:
     *     array<int, string>: Lower-case host names.
     */
    public static function mediaLinkHosts(): array
    {
        return array_values(array_filter(array_map(
            fn ($host) => strtolower(trim($host)),
            explode(',', (string) self::get('academic.media_link_hosts'))
        )));
    }
}
