<?php

namespace App\Http\Controllers\Admin\Privacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Privacy\Admin\PolicyDocumentRequest;
use App\Http\Requests\Privacy\Admin\PublishPolicyRequest;
use App\Models\Privacy\PolicyDocument;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\PolicyDiff;
use App\Services\Privacy\PolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Versioned policy documents: drafts, publishing with an effective date, re-acceptance
 * and version comparison (UU 27/2022 Pasal 21 ayat (2); PP 33/2026 Pasal 62 ayat (5)).
 * Published versions are immutable; a change is always a new version.
 */
class PolicyDocumentController extends Controller
{
    public function __construct(private readonly PolicyService $policies)
    {
    }

    /**
     * Versions of every document type (?jenis= filter).
     */
    public function index(Request $request): View
    {
        $type = array_key_exists((string) $request->query('jenis'), PolicyDocument::TYPES) ? $request->query('jenis') : null;

        $documents = PolicyDocument::query()
            ->select(['id', 'type', 'revision', 'version', 'title', 'status', 'requires_reacceptance', 'effective_at', 'published_at', 'updated_at'])
            ->withCount('acceptances')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('type')->orderByDesc('revision')
            ->paginate(25)->withQueryString();

        $current = collect(array_keys(PolicyDocument::TYPES))->mapWithKeys(fn ($t) => [$t => $this->policies->currentMeta($t)]);

        return view('admin.privacy.policies.index', compact('documents', 'type', 'current'));
    }

    /**
     * New draft, prefilled from the current version of the type (or the bundled text).
     */
    public function create(Request $request): View
    {
        $type = array_key_exists((string) $request->query('jenis'), PolicyDocument::TYPES) ? $request->query('jenis') : 'privacy_notice';
        $base = $this->policies->currentOrDefault($type);
        $next = PolicyDocument::nextRevision($type);

        $document = new PolicyDocument([
            'type' => $type,
            'version' => $base && $base->exists ? $this->suggestVersion($base->version) : '1.0',
            'title' => $base?->title ?? PolicyDocument::TYPES[$type],
            'summary' => $base?->summary,
            'body' => $base?->body ?? '',
        ]);

        return view('admin.privacy.policies.form', [
            'document' => $document,
            'variables' => $this->policies->variableNames(),
            'revision' => $next,
        ]);
    }

    /**
     * Save a new draft.
     */
    public function store(PolicyDocumentRequest $request): RedirectResponse
    {
        $type = $request->validated('type');
        $document = PolicyDocument::create($request->safe()->only(['type', 'version', 'title', 'summary', 'body', 'changelog']) + [
            'revision' => PolicyDocument::nextRevision($type),
            'status' => 'draft',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.privacy.policies.show', $document)->with('success', 'Draf disimpan. Tinjau pratinjau lalu terbitkan bila sudah siap.');
    }

    /**
     * Preview, metadata and publish form.
     */
    public function show(PolicyDocument $policy): View
    {
        $policy->loadCount('acceptances')->load(['publisher:id,name', 'author:id,name']);

        return view('admin.privacy.policies.show', [
            'document' => $policy,
            'html' => $this->policies->render($policy),
            'previous' => $this->previous($policy),
            'isCurrent' => $this->policies->currentMeta($policy->type)?->id === $policy->id,
            'reacceptable' => in_array($policy->type, PolicyService::ACCEPTANCE_TYPES, true),
        ]);
    }

    /**
     * Edit a draft.
     */
    public function edit(PolicyDocument $policy): View|RedirectResponse
    {
        if (! $policy->isDraft()) {
            return redirect()->route('admin.privacy.policies.show', $policy)->with('error', 'Versi yang sudah terbit tidak dapat diubah. Buat versi baru.');
        }

        return view('admin.privacy.policies.form', [
            'document' => $policy,
            'variables' => $this->policies->variableNames(),
            'revision' => $policy->revision,
        ]);
    }

    /**
     * Update a draft.
     */
    public function update(PolicyDocumentRequest $request, PolicyDocument $policy): RedirectResponse
    {
        if (! $policy->isDraft()) {
            return redirect()->route('admin.privacy.policies.show', $policy)->with('error', 'Versi yang sudah terbit tidak dapat diubah. Buat versi baru.');
        }

        $policy->fill($request->safe()->only(['version', 'title', 'summary', 'body', 'changelog']) + ['updated_by' => $request->user()->id])->save();

        return redirect()->route('admin.privacy.policies.show', $policy)->with('success', 'Draf diperbarui.');
    }

    /**
     * Delete a draft.
     */
    public function destroy(PolicyDocument $policy): RedirectResponse
    {
        if (! $policy->isDraft()) {
            return back()->with('error', 'Versi yang sudah terbit adalah bukti hukum dan tidak dapat dihapus.');
        }

        $policy->delete();

        return redirect()->route('admin.privacy.policies.index')->with('success', 'Draf dihapus.');
    }

    /**
     * Publish a draft now or on a future date (people are informed before it applies).
     */
    public function publish(PublishPolicyRequest $request, PolicyDocument $policy): RedirectResponse
    {
        if (! $policy->isDraft()) {
            return back()->with('error', 'Versi ini sudah terbit.');
        }

        $tz = (string) setting('general.timezone', 'Asia/Jakarta');
        $effective = $request->filled('effective_at') ? Carbon::parse($request->validated('effective_at'), $tz)->utc() : now();
        $effective = $effective->isPast() ? now() : $effective;

        $this->policies->publish($policy, $effective, $request->boolean('requires_reacceptance'), $request->user());

        $message = $policy->isScheduled()
            ? 'Versi '.$policy->version.' terbit dan berlaku mulai '.PolicyService::date($policy->effective_at, true).'. Pengunjung sudah dapat membacanya sebelum berlaku.'
            : 'Versi '.$policy->version.' terbit dan berlaku sekarang.';

        if ($policy->requires_reacceptance) {
            $message .= ' Pengguna akan diminta menyetujui ulang saat masuk.';
        }

        return redirect()->route('admin.privacy.policies.show', $policy)->with('success', $message);
    }

    /**
     * Line diff against another version of the same type (default: the previous one).
     */
    public function diff(Request $request, PolicyDocument $policy): View
    {
        $versions = PolicyDocument::query()->where('type', $policy->type)->where('id', '!=', $policy->id)
            ->orderByDesc('revision')->get(['id', 'version', 'revision', 'status']);

        $against = $request->filled('dengan')
            ? $versions->firstWhere('id', (int) $request->query('dengan'))
            : $this->previous($policy);
        $against = $against ? PolicyDocument::find($against->id) : null;

        $ops = $against ? PolicyDiff::lines($against->body, $policy->body) : [];

        ConsentService::audit('policy.compared', $policy, ['against' => $against?->id]);

        return view('admin.privacy.policies.diff', [
            'document' => $policy,
            'against' => $against,
            'versions' => $versions,
            'hunks' => PolicyDiff::fold($ops),
            'stats' => PolicyDiff::stats($ops),
        ]);
    }

    /** The version just before this one (same type). */
    private function previous(PolicyDocument $policy): ?PolicyDocument
    {
        return PolicyDocument::query()->where('type', $policy->type)->where('revision', '<', $policy->revision)
            ->orderByDesc('revision')->first(['id', 'version', 'revision', 'status']);
    }

    /** "1.0" → "1.1", "2" → "3"; anything else gets ".1" appended. */
    private function suggestVersion(string $version): string
    {
        if (preg_match('/^(\d+)\.(\d+)$/', $version, $m)) {
            return $m[1].'.'.((int) $m[2] + 1);
        }

        return ctype_digit($version) ? (string) ((int) $version + 1) : $version.'.1';
    }
}
