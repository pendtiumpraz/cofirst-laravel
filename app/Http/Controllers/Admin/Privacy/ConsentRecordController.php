<?php

namespace App\Http\Controllers\Admin\Privacy;

use App\Http\Controllers\Controller;
use App\Models\Privacy\ConsentPurpose;
use App\Models\Privacy\ConsentRecord;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\PolicyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Read-only view of the consent ledger, the controller's proof of consent
 * (UU 27/2022 Pasal 24; PP 33/2026 Pasal 36), with filters and CSV export.
 * Every view and export is written to the audit log.
 */
class ConsentRecordController extends Controller
{
    /**
     * Filtered, paginated ledger.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $records = $this->query($filters)
            ->with(['subject:id,name,email', 'actor:id,name', 'purpose:id,key,name'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        ConsentService::audit('consent_records.viewed', null, ['filters' => array_keys(array_filter($filters)), 'page' => $records->currentPage()]);

        $latest = ConsentRecord::query()->selectRaw('max(id) as id')->whereNotNull('subject_user_id')->groupBy('subject_user_id', 'purpose_key');

        return view('admin.privacy.consent-records.index', [
            'records' => $records,
            'filters' => $filters,
            'purposes' => ConsentPurpose::query()->ordered()->pluck('name', 'key'),
            'stats' => [
                'active' => ConsentRecord::query()->whereIn('id', $latest)->where('decision', 'granted')->count(),
                'withdrawn' => ConsentRecord::query()->where('decision', 'withdrawn')->where('created_at', '>=', now()->subDays(30))->count(),
                'parental' => ConsentRecord::query()->where('method', 'parental')->where('decision', 'granted')->count(),
            ],
        ]);
    }

    /**
     * CSV export of the filtered ledger (UTF-8 with BOM for spreadsheet apps).
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $query = $this->query($filters)->with(['subject:id,name,email', 'actor:id,name', 'purpose:id,key,name', 'policyDocument:id,version']);
        $count = (clone $query)->count();

        ConsentService::audit('consent_records.exported', null, ['filters' => array_keys(array_filter($filters)), 'rows' => $count]);

        $filename = 'catatan-persetujuan-'.now()->setTimezone((string) setting('general.timezone', 'Asia/Jakarta'))->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Waktu', 'Subjek', 'Email subjek', 'Tujuan', 'Kunci tujuan', 'Versi tujuan', 'Keputusan', 'Metode', 'Pelaku', 'Email pelaku (wali)', 'Versi kebijakan privasi', 'Alamat IP']);

            $query->lazyById(500)->each(function (ConsentRecord $r) use ($out) {
                fputcsv($out, array_map(fn ($v) => $this->cell($v), [
                    $r->id,
                    PolicyService::date($r->created_at, true),
                    $r->subject?->name ?? '—',
                    $r->subject?->email ?? '',
                    $r->purpose?->name ?? $r->purpose_key,
                    $r->purpose_key,
                    $r->purpose_version,
                    $r->decision_label,
                    $r->method_label,
                    $r->actor_label,
                    $r->actor_email ?? '',
                    $r->policyDocument?->version ?? '',
                    $r->ip_address ?? '',
                ]));
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, private']);
    }

    /**
     * Normalised filters from the query string.
     *
     * @return array<string, string|null>
     */
    private function filters(Request $request): array
    {
        $date = fn (?string $v) => $v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;

        return [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 100) ?: null,
            'purpose' => array_key_exists((string) $request->query('purpose'), ConsentPurpose::query()->pluck('id', 'key')->all()) ? $request->query('purpose') : null,
            'decision' => array_key_exists((string) $request->query('decision'), ConsentRecord::DECISIONS) ? $request->query('decision') : null,
            'method' => array_key_exists((string) $request->query('method'), ConsentRecord::METHODS) ? $request->query('method') : null,
            'from' => $date($request->query('from')),
            'to' => $date($request->query('to')),
        ];
    }

    /**
     * Ledger query for the filters.
     */
    private function query(array $filters): Builder
    {
        $tz = (string) setting('general.timezone', 'Asia/Jakarta');

        return ConsentRecord::query()
            ->when($filters['purpose'], fn ($q, $v) => $q->where('purpose_key', $v))
            ->when($filters['decision'], fn ($q, $v) => $q->where('decision', $v))
            ->when($filters['method'], fn ($q, $v) => $q->where('method', $v))
            ->when($filters['from'], fn ($q, $v) => $q->where('created_at', '>=', Carbon::parse($v, $tz)->startOfDay()->utc()))
            ->when($filters['to'], fn ($q, $v) => $q->where('created_at', '<=', Carbon::parse($v, $tz)->endOfDay()->utc()))
            ->when($filters['q'], function ($q, $term) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
                $q->where(function ($w) use ($like) {
                    $w->whereHas('subject', fn ($s) => $s->withTrashed()->where('name', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhere('actor_email', 'like', $like);
                });
            });
    }

    /**
     * Neutralise spreadsheet formulas in a CSV cell (CSV injection).
     */
    private function cell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
