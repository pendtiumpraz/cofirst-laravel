<?php

namespace App\Http\Controllers\Privacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Privacy\PolicyAcceptanceRequest;
use App\Models\Privacy\PolicyDocument;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\PolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Re-acceptance page shown by EnsurePolicyAccepted after a new privacy notice or terms
 * version is published (UU 27/2022 Pasal 21 ayat (2); PP 33/2026 Pasal 62 ayat (5)).
 */
class PolicyAcceptanceController extends Controller
{
    public function __construct(
        private readonly PolicyService $policies,
        private readonly ConsentService $consents,
    ) {
    }

    /**
     * What changed and the acceptance form; back to the intended page when nothing is pending.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $pending = $this->policies->pendingAcceptances($request->user());

        if ($pending->isEmpty()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('privacy.accept', [
            'documents' => PolicyDocument::query()->whereIn('id', $pending->pluck('id'))->orderBy('type')->get(),
            'isChild' => $this->consents->isChild($request->user()),
            'privacyCentreUrl' => $this->policies->variables()['url_privacy_centre'],
        ]);
    }

    /**
     * Record acceptance of every pending version, then continue.
     */
    public function store(PolicyAcceptanceRequest $request): RedirectResponse
    {
        $user = $request->user();
        $pending = $this->policies->pendingAcceptances($user);

        if ($pending->isEmpty()) {
            return redirect()->intended(route('dashboard'));
        }

        // Reason: if another version was published while the page was open, show the new one.
        if ($pending->pluck('id')->sort()->values()->all() !== collect($request->validated('documents'))->map(fn ($id) => (int) $id)->sort()->values()->all()) {
            return redirect()->route('privacy.accept')->with('status', 'Ada versi yang lebih baru. Mohon tinjau kembali sebelum menyetujui.');
        }

        $this->policies->accept($user, PolicyDocument::query()->whereIn('id', $pending->pluck('id'))->get(), 'reacceptance', $request, $user);
        $request->session()->forget('privacy.accepted_baseline');

        return redirect()->intended(route('dashboard'))->with('success', 'Terima kasih. Persetujuan Anda atas kebijakan terbaru telah dicatat.');
    }
}
