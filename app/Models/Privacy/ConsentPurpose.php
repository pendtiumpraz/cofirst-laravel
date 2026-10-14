<?php

namespace App\Models\Privacy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A processing purpose and its legal basis (UU 27/2022 Pasal 20; PP 33/2026 Pasal 30, 67-68).
 *
 * The register drives the consent UI (registration, cookie banner, privacy centre, parental
 * consent) and is listed in the privacy notice. Purposes whose legal basis is not `consent`
 * are shown for transparency only; nobody "grants" them.
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $description
 * @property string $category essential|functional|analytics|marketing|media_publication|leaderboard_public
 * @property string $legal_basis consent|contract|legal_obligation|vital_interest|public_task|legitimate_interest
 * @property bool $is_required
 * @property array<int, string>|null $applies_to roles (student, parent, teacher, admin, superadmin, finance, guest)
 * @property string $audience all|adults|children
 * @property int $version
 * @property bool $is_active
 */
class ConsentPurpose extends Model
{
    public const CATEGORIES = [
        'essential' => 'Esensial',
        'functional' => 'Fungsional',
        'analytics' => 'Analitik',
        'marketing' => 'Pemasaran',
        'media_publication' => 'Publikasi foto & karya',
        'leaderboard_public' => 'Papan peringkat',
    ];

    /** Legal bases of UU 27/2022 Pasal 20 ayat (2) huruf a-f. */
    public const LEGAL_BASES = [
        'consent' => 'Persetujuan (Pasal 20 ayat (2) huruf a)',
        'contract' => 'Pelaksanaan perjanjian (huruf b)',
        'legal_obligation' => 'Kewajiban hukum (huruf c)',
        'vital_interest' => 'Kepentingan vital (huruf d)',
        'public_task' => 'Tugas kepentingan umum (huruf e)',
        'legitimate_interest' => 'Kepentingan yang sah (huruf f)',
    ];

    public const AUDIENCES = [
        'all' => 'Semua usia',
        'adults' => 'Hanya dewasa',
        'children' => 'Hanya anak (melalui orang tua/wali)',
    ];

    /** Roles a purpose can apply to; `guest` = visitors without an account (cookies). */
    public const ROLES = [
        'student' => 'Siswa',
        'parent' => 'Orang tua',
        'teacher' => 'Guru',
        'admin' => 'Admin',
        'superadmin' => 'Superadmin',
        'finance' => 'Keuangan',
        'guest' => 'Pengunjung',
    ];

    /** Categories handled by the cookie banner instead of account forms. */
    public const COOKIE_CATEGORIES = ['functional', 'analytics'];

    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'legal_basis',
        'is_required',
        'applies_to',
        'audience',
        'data_used',
        'version',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'applies_to' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Built-in purpose register (seeded by PrivacySeeder; keys are used by other modules).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaults(): array
    {
        $staff = ['teacher', 'admin', 'superadmin', 'finance'];
        $everyone = array_merge(['student', 'parent'], $staff);

        return [
            'essential_service' => [
                'name' => 'Layanan belajar inti',
                'description' => 'Membuat dan mengelola akun, menjadwalkan kelas, mencatat kehadiran dan perkembangan belajar, menerbitkan sertifikat, serta menagih biaya kursus. Diperlukan untuk menjalankan perjanjian layanan (UU PDP Pasal 20 ayat (2) huruf b).',
                'category' => 'essential', 'legal_basis' => 'contract', 'is_required' => true,
                'applies_to' => $everyone, 'audience' => 'all', 'sort_order' => 10,
                'data_used' => 'Identitas akun, profil, jadwal, kehadiran, laporan belajar, sertifikat, tagihan',
            ],
            'child_data_processing' => [
                'name' => 'Pemrosesan data anak untuk kegiatan belajar',
                'description' => 'Persetujuan orang tua/wali agar data anak (nama, tanggal lahir, kehadiran, laporan dan nilai, karya, poin, sertifikat, pesan dengan pengajar) diproses untuk menyelenggarakan kelas. Wajib menurut UU PDP Pasal 25 ayat (2).',
                'category' => 'essential', 'legal_basis' => 'consent', 'is_required' => true,
                'applies_to' => ['student'], 'audience' => 'children', 'sort_order' => 20,
                'data_used' => 'Data akun dan data belajar anak',
            ],
            'legal_records' => [
                'name' => 'Kewajiban hukum: pembukuan dan perpajakan',
                'description' => 'Menyimpan tagihan, pembayaran dan bukti transaksi selama masa simpan yang diwajibkan undang-undang (UU KUP Pasal 28 ayat (11); UU No. 8 Tahun 1997 Pasal 11). Dasar: UU PDP Pasal 20 ayat (2) huruf c.',
                'category' => 'essential', 'legal_basis' => 'legal_obligation', 'is_required' => true,
                'applies_to' => ['student', 'parent'], 'audience' => 'all', 'sort_order' => 30,
                'data_used' => 'Tagihan, pembayaran, bukti transfer, nama pembayar',
            ],
            'security_logging' => [
                'name' => 'Keamanan akun dan pencegahan penyalahgunaan',
                'description' => 'Mencatat aktivitas masuk, perubahan data penting dan akses ke data pribadi untuk melindungi akun dan mendeteksi penyalahgunaan. Dasar: kepentingan yang sah (UU PDP Pasal 20 ayat (2) huruf f) setelah penilaian keseimbangan.',
                'category' => 'essential', 'legal_basis' => 'legitimate_interest', 'is_required' => true,
                'applies_to' => array_merge($everyone, ['guest']), 'audience' => 'all', 'sort_order' => 40,
                'data_used' => 'Alamat IP, jenis perangkat dan peramban, waktu akses',
            ],
            'cookies_functional' => [
                'name' => 'Cookie fungsional',
                'description' => 'Mengingat pilihan tambahan dan memuat konten pihak ketiga yang Anda minta, misalnya peta lokasi kelas atau video yang disematkan. Tanpa persetujuan ini, situs tetap berfungsi penuh.',
                'category' => 'functional', 'legal_basis' => 'consent', 'is_required' => false,
                'applies_to' => array_merge($everyone, ['guest']), 'audience' => 'all', 'sort_order' => 50,
                'data_used' => 'Pengenal cookie, alamat IP (oleh penyedia konten tersemat)',
            ],
            'cookies_analytics' => [
                'name' => 'Cookie analitik',
                'description' => 'Statistik kunjungan agregat untuk memahami halaman mana yang membantu dan memperbaiki situs. Tidak pernah aktif untuk akun anak dan tidak dipakai untuk iklan.',
                'category' => 'analytics', 'legal_basis' => 'consent', 'is_required' => false,
                'applies_to' => array_merge($everyone, ['guest']), 'audience' => 'adults', 'sort_order' => 60,
                'data_used' => 'Halaman yang dikunjungi, perkiraan lokasi kota, jenis perangkat',
            ],
            'marketing_communications' => [
                'name' => 'Info program dan promo',
                'description' => 'Kami mengirim kabar program baru, jadwal kelas percobaan dan promo melalui email atau WhatsApp, paling banyak dua kali sebulan. Data tidak dibagikan ke pihak lain untuk pemasaran. Berhenti kapan saja lewat tautan di setiap pesan atau Pusat Privasi (PP 33/2026 Pasal 35 ayat (2)).',
                'category' => 'marketing', 'legal_basis' => 'consent', 'is_required' => false,
                'applies_to' => ['student', 'parent'], 'audience' => 'adults', 'sort_order' => 70,
                'data_used' => 'Nama, email, nomor WhatsApp',
            ],
            'media_publication' => [
                'name' => 'Publikasi foto dan karya',
                'description' => 'Menampilkan foto kegiatan kelas dan karya proyek bersama nama depan dan inisial (tanpa nama lengkap) di situs, galeri publik dan media sosial Coding First.',
                'category' => 'media_publication', 'legal_basis' => 'consent', 'is_required' => false,
                'applies_to' => ['student'], 'audience' => 'all', 'sort_order' => 80,
                'data_used' => 'Foto, karya proyek, nama depan dan inisial, usia',
            ],
            'leaderboard_public' => [
                'name' => 'Nama di papan peringkat',
                'description' => 'Menampilkan nama depan (atau inisial, sesuai pengaturan sekolah) dan poin di papan peringkat yang dapat dilihat siswa dan orang tua lain. Tanpa persetujuan ini, siswa tetap mendapat poin tetapi tampil anonim.',
                'category' => 'leaderboard_public', 'legal_basis' => 'consent', 'is_required' => false,
                'applies_to' => ['student'], 'audience' => 'all', 'sort_order' => 90,
                'data_used' => 'Nama depan atau inisial, poin, lencana',
            ],
            'testimonial_publication' => [
                'name' => 'Publikasi testimoni',
                'description' => 'Menampilkan testimoni Anda beserta nama dan, bila Anda izinkan, nama depan anak di situs Coding First.',
                'category' => 'media_publication', 'legal_basis' => 'consent', 'is_required' => false,
                'applies_to' => ['parent'], 'audience' => 'adults', 'sort_order' => 100,
                'data_used' => 'Nama, isi testimoni, nama depan anak',
            ],
        ];
    }

    /** Consent ledger rows for this purpose. */
    public function records(): HasMany
    {
        return $this->hasMany(ConsentRecord::class);
    }

    /** Only purposes in use. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Display order: sort_order, then name. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** Whether people actually decide on this purpose (legal basis = consent). */
    public function isConsentBased(): bool
    {
        return $this->legal_basis === 'consent';
    }

    /** Whether the purpose is decided through the cookie banner. */
    public function isCookie(): bool
    {
        return in_array($this->category, self::COOKIE_CATEGORIES, true);
    }

    /**
     * Whether the purpose applies to a set of roles (any match) and an age group.
     *
     * Args:
     *     roles (array<int, string>): Role names of the subject ('guest' for visitors).
     *     isChild (bool): Whether the subject is a child.
     *
     * Returns:
     *     bool: True when the purpose is relevant for that subject.
     */
    public function appliesTo(array $roles, bool $isChild = false): bool
    {
        if ($this->audience === 'adults' && $isChild) {
            return false;
        }

        if ($this->audience === 'children' && ! $isChild) {
            return false;
        }

        $targets = $this->applies_to ?: array_keys(self::ROLES);

        return array_intersect($targets, $roles) !== [];
    }

    /** Indonesian label of the category. */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /** Indonesian label of the legal basis. */
    public function getLegalBasisLabelAttribute(): string
    {
        return self::LEGAL_BASES[$this->legal_basis] ?? $this->legal_basis;
    }

    /** Short legal-basis label for tables ("Persetujuan", "Perjanjian", …). */
    public function getLegalBasisShortAttribute(): string
    {
        return [
            'consent' => 'Persetujuan',
            'contract' => 'Perjanjian',
            'legal_obligation' => 'Kewajiban hukum',
            'vital_interest' => 'Kepentingan vital',
            'public_task' => 'Tugas kepentingan umum',
            'legitimate_interest' => 'Kepentingan yang sah',
        ][$this->legal_basis] ?? $this->legal_basis;
    }

    /** UU PDP reference of the legal basis ("Pasal 20 (2) a"). */
    public function getLegalBasisArticleAttribute(): string
    {
        $letter = array_search($this->legal_basis, array_keys(self::LEGAL_BASES), true);

        return $letter === false ? '' : 'Pasal 20 ayat (2) huruf '.chr(ord('a') + $letter);
    }

    /** Who the purpose applies to, summarised ("Semua pengguna & pengunjung", "Siswa, Orang tua"). */
    public function getAudienceSummaryAttribute(): string
    {
        $targets = $this->applies_to ?: array_keys(self::ROLES);
        $accounts = array_diff(array_keys(self::ROLES), ['guest']);
        $guest = in_array('guest', $targets, true);

        if (array_diff($accounts, $targets) === []) {
            return $guest ? 'Semua pengguna & pengunjung' : 'Semua pengguna';
        }

        return collect($targets)->map(fn ($r) => self::ROLES[$r] ?? $r)->implode(', ');
    }

    /** Badge tone for the category. */
    public function getCategoryToneAttribute(): string
    {
        return match ($this->category) {
            'essential' => 'neutral',
            'functional' => 'info',
            'analytics' => 'warning',
            'marketing' => 'accent',
            default => 'primary',
        };
    }

    /** Short hash of the wording people agreed to (stored as evidence). */
    public function textHash(): string
    {
        return substr(hash('sha256', $this->name."\n".$this->description.'|v'.$this->version), 0, 16);
    }
}
