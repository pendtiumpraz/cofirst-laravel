<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

/**
 * Living style guide of the design system (GET /admin/ui-kit, admin + superadmin).
 *
 * Renders every <x-ui.*> component and token in a standalone page so the
 * preview does not depend on the dashboard shell. Query switches preview
 * the A/B theme, colour mode, density and KPI style without saving anything.
 */
class UiKitController extends Controller
{
    /** Allowed values per preview switch; the first one is the fallback. */
    private const SWITCHES = [
        'theme' => ['a', 'b'],
        'mode' => ['light', 'dark', 'system'],
        'density' => ['comfortable', 'compact'],
        'stat' => ['strip', 'cards'],
    ];

    /**
     * Show the UI kit.
     *
     * Args:
     *     request (Request): ?theme=a|b&mode=light|dark|system&density=…&stat=…&errors=1
     *
     * Returns:
     *     View: the uikit.index page.
     */
    public function index(Request $request): View
    {
        $preview = [
            'theme' => $this->pick($request, 'theme', setting('appearance.theme', 'a')),
            'mode' => $this->pick($request, 'mode', 'light'),
            'density' => $this->pick($request, 'density', setting('appearance.density', 'comfortable')),
            'stat' => $this->pick($request, 'stat', setting('appearance.stat_style', 'strip')),
        ];

        if ($request->boolean('errors')) {
            // Reason: demonstrates field error states and the validation toast
            // without submitting a form (shared after the session errors).
            view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag([
                'demo_email' => ['Format email tidak valid (contoh: nama@domain.id).'],
                'demo_program' => ['Pilih salah satu program.'],
                'demo_consent' => ['Persetujuan Kebijakan Privasi wajib dicentang.'],
                'demo_request' => ['Pilih jenis permintaan.'],
            ])));
        }

        return view('uikit.index', [
            'preview' => $preview,
            'users' => $this->demoUsers($request),
            'revenue' => [
                'labels' => ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
                'data' => [31500000, 33800000, 32000000, 41000000, 43250000, 48200000],
            ],
            'attendance' => [
                'labels' => ['Mg 1', 'Mg 2', 'Mg 3', 'Mg 4', 'Mg 5', 'Mg 6', 'Mg 7', 'Mg 8'],
                'present' => [88, 91, 86, 93, 95, 92, 94, 96],
                'target' => [90, 90, 90, 90, 90, 90, 90, 90],
            ],
            'payments' => [
                'labels' => ['Lunas', 'Menunggu', 'Jatuh tempo', 'Terlambat'],
                'data' => [124, 18, 9, 4],
            ],
        ]);
    }

    /**
     * Resolve one preview switch from the query string, falling back to a default.
     */
    private function pick(Request $request, string $key, mixed $default): string
    {
        $allowed = self::SWITCHES[$key];
        $value = $request->query($key);

        if (is_string($value) && in_array($value, $allowed, true)) {
            return $value;
        }

        return in_array($default, $allowed, true) ? $default : $allowed[0];
    }

    /**
     * Fictional rows for the table demo (sortable, paginated: 5 per page).
     */
    private function demoUsers(Request $request): LengthAwarePaginator
    {
        $rows = collect([
            ['name' => 'Andi Pratama', 'email' => 'andi.p@contoh.id', 'role' => 'Siswa', 'status' => 'Aktif', 'invoice' => 'INV-2026-0142', 'amount' => 1250000, 'joined' => '2026-01-12'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.n@contoh.id', 'role' => 'Orang tua', 'status' => 'Aktif', 'invoice' => 'INV-2026-0139', 'amount' => 750000, 'joined' => '2026-02-03'],
            ['name' => 'Kenji Watanabe', 'email' => 'kenji.w@contoh.id', 'role' => 'Guru', 'status' => 'Aktif', 'invoice' => '—', 'amount' => 0, 'joined' => '2025-11-20'],
            ['name' => 'Rina Kartika', 'email' => 'rina.k@contoh.id', 'role' => 'Siswa', 'status' => 'Menunggu', 'invoice' => 'INV-2026-0151', 'amount' => 1250000, 'joined' => '2026-09-30'],
            ['name' => 'Budi Santoso', 'email' => 'budi.s@contoh.id', 'role' => 'Keuangan', 'status' => 'Aktif', 'invoice' => '—', 'amount' => 0, 'joined' => '2025-08-01'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.l@contoh.id', 'role' => 'Orang tua', 'status' => 'Terlambat', 'invoice' => 'INV-2026-0117', 'amount' => 1500000, 'joined' => '2026-03-15'],
            ['name' => 'Yuki Tanaka', 'email' => 'yuki.t@contoh.id', 'role' => 'Guru', 'status' => 'Cuti', 'invoice' => '—', 'amount' => 0, 'joined' => '2025-06-09'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.n@contoh.id', 'role' => 'Siswa', 'status' => 'Aktif', 'invoice' => 'INV-2026-0148', 'amount' => 950000, 'joined' => '2026-05-21'],
            ['name' => 'Maya Putri', 'email' => 'maya.p@contoh.id', 'role' => 'Siswa', 'status' => 'Arsip', 'invoice' => 'INV-2025-0990', 'amount' => 1250000, 'joined' => '2025-02-10'],
            ['name' => 'Agus Wijaya', 'email' => 'agus.w@contoh.id', 'role' => 'Admin', 'status' => 'Aktif', 'invoice' => '—', 'amount' => 0, 'joined' => '2025-01-04'],
            ['name' => 'Putri Ayu', 'email' => 'putri.a@contoh.id', 'role' => 'Siswa', 'status' => 'Menunggu', 'invoice' => 'INV-2026-0155', 'amount' => 750000, 'joined' => '2026-10-02'],
            ['name' => 'Hiroshi Sato', 'email' => 'hiroshi.s@contoh.id', 'role' => 'Guru', 'status' => 'Aktif', 'invoice' => '—', 'amount' => 0, 'joined' => '2025-07-18'],
        ]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $rows = $rows->filter(fn (array $row) => str_contains(mb_strtolower($row['name'].' '.$row['email']), mb_strtolower($search)));
        }

        $role = $request->query('role');
        if (is_string($role) && $role !== '') {
            $rows = $rows->where('role', $role);
        }

        $sort = in_array($request->query('sort'), ['name', 'amount', 'joined'], true) ? $request->query('sort') : 'name';
        $rows = $rows->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $request->query('direction') === 'desc')->values();

        $perPage = 5;
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }
}
