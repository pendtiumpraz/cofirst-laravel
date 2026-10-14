<?php

namespace App\Http\Controllers\Admin\Privacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Privacy\Admin\ConsentPurposeRequest;
use App\Models\Privacy\ConsentPurpose;
use App\Models\Privacy\ConsentRecord;
use App\Services\Privacy\ConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Register of processing purposes and their legal basis (UU 27/2022 Pasal 20;
 * PP 33/2026 Pasal 30 & 67-68). Purpose keys are an API for other modules
 * (ConsentService::hasConsent), so they cannot change after creation.
 */
class ConsentPurposeController extends Controller
{
    /**
     * All purposes with their number of active grants.
     */
    public function index(): View
    {
        $purposes = ConsentPurpose::query()->ordered()->withCount('records')->get();

        // Reason: one grouped query instead of one count per purpose.
        $latest = ConsentRecord::query()->selectRaw('max(id) as id')->whereNotNull('subject_user_id')->groupBy('subject_user_id', 'purpose_key');
        $active = ConsentRecord::query()
            ->whereIn('id', $latest)
            ->where('decision', 'granted')
            ->select('purpose_key', DB::raw('count(*) as total'))
            ->groupBy('purpose_key')
            ->pluck('total', 'purpose_key');

        $builtIn = array_keys(ConsentPurpose::defaults());

        return view('admin.privacy.consent-purposes.index', compact('purposes', 'active', 'builtIn'));
    }

    /**
     * New purpose form.
     */
    public function create(): View
    {
        return view('admin.privacy.consent-purposes.form', ['purpose' => new ConsentPurpose([
            'category' => 'marketing',
            'legal_basis' => 'consent',
            'audience' => 'all',
            'applies_to' => ['student', 'parent'],
            'is_active' => true,
        ])]);
    }

    /**
     * Save a new purpose.
     */
    public function store(ConsentPurposeRequest $request): RedirectResponse
    {
        $purpose = ConsentPurpose::create($this->attributes($request) + [
            'key' => $request->validated('key'),
            'version' => 1,
        ]);

        ConsentService::audit('consent_purpose.created', $purpose, ['key' => $purpose->key]);

        return redirect()->route('admin.privacy.consent-purposes.index')
            ->with('success', "Tujuan \"{$purpose->name}\" ditambahkan.");
    }

    /**
     * Edit form.
     */
    public function edit(ConsentPurpose $purpose): View
    {
        return view('admin.privacy.consent-purposes.form', compact('purpose'));
    }

    /**
     * Update a purpose; "naikkan versi" makes earlier consents stale (re-consent).
     */
    public function update(ConsentPurposeRequest $request, ConsentPurpose $purpose): RedirectResponse
    {
        $purpose->fill($this->attributes($request));

        if ($request->boolean('bump_version')) {
            $purpose->version++;
        }

        $purpose->save();

        ConsentService::audit('consent_purpose.updated', $purpose, ['key' => $purpose->key, 'version' => $purpose->version]);

        $message = $request->boolean('bump_version')
            ? "Tujuan diperbarui ke versi {$purpose->version}. Persetujuan lama tidak lagi berlaku sampai pengguna menyetujui ulang."
            : 'Perubahan tujuan disimpan.';

        return redirect()->route('admin.privacy.consent-purposes.index')->with('success', $message);
    }

    /**
     * Delete an unused purpose; purposes with ledger rows can only be deactivated.
     */
    public function destroy(ConsentPurpose $purpose): RedirectResponse
    {
        // Reason: other modules call hasConsent() with the built-in keys; deleting one would silently
        // turn their checks off. Built-in purposes can only be deactivated.
        if (array_key_exists($purpose->key, ConsentPurpose::defaults())) {
            return back()->with('error', 'Tujuan bawaan dipakai modul lain sehingga tidak dapat dihapus. Nonaktifkan saja bila tidak diperlukan.');
        }

        if ($purpose->records()->exists()) {
            return back()->with('error', 'Tujuan ini sudah memiliki catatan persetujuan sehingga tidak dapat dihapus. Nonaktifkan saja agar riwayatnya tetap tersimpan.');
        }

        $name = $purpose->name;
        $purpose->delete();
        ConsentService::audit('consent_purpose.deleted', null, ['key' => $purpose->key]);

        return redirect()->route('admin.privacy.consent-purposes.index')->with('success', "Tujuan \"{$name}\" dihapus.");
    }

    /**
     * Editable attributes from the request (key and version are handled separately).
     *
     * @return array<string, mixed>
     */
    private function attributes(ConsentPurposeRequest $request): array
    {
        return [
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'category' => $request->validated('category'),
            'legal_basis' => $request->validated('legal_basis'),
            'is_required' => $request->boolean('is_required'),
            'applies_to' => array_values($request->validated('applies_to')),
            'audience' => $request->validated('audience'),
            'data_used' => $request->validated('data_used'),
            'sort_order' => (int) $request->validated('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
