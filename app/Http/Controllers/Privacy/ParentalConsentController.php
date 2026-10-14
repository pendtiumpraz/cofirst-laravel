<?php

namespace App\Http\Controllers\Privacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Privacy\ParentalConsentResendRequest;
use App\Http\Requests\Privacy\ParentalConsentResponseRequest;
use App\Models\Privacy\ParentalConsentRequest;
use App\Models\User;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\ParentalConsentService;
use App\Services\Privacy\PolicyService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Guardian side of parental consent (UU 27/2022 Pasal 25 ayat (2); PP 33/2026 Pasal 38).
 *
 * The guardian opens the signed link from the e-mail, reads the children's notice and
 * decides: approve (with optional consents, all unticked by default) or decline.
 */
class ParentalConsentController extends Controller
{
    public function __construct(
        private readonly ParentalConsentService $parental,
        private readonly ConsentService $consents,
        private readonly PolicyService $policies,
    ) {
    }

    /**
     * Consent form, or the status page when the request is closed / the link is invalid.
     */
    public function show(Request $request, string $token): View|Response
    {
        [$state, $consentRequest] = $this->resolve($request, $token);

        if ($state !== 'open') {
            return $this->status($state, $consentRequest, $token);
        }

        $child = $consentRequest->child;
        $purposes = $this->consents->purposesFor($child, true)->reject(fn ($p) => $p->isCookie());
        $notice = $this->policies->currentOrDefault('children_notice');

        return view('privacy.parental-consent', [
            'consentRequest' => $consentRequest,
            'child' => $child,
            'required' => $purposes->where('is_required', true)->values(),
            'optional' => $purposes->where('is_required', false)->values(),
            'notice' => $notice,
            'noticeHtml' => $notice ? $this->policies->render($notice) : null,
            'action' => $request->fullUrl(),
        ]);
    }

    /**
     * Record the guardian's decision, then show the result (PRG to the same signed link).
     */
    public function respond(ParentalConsentResponseRequest $request, string $token): RedirectResponse|Response
    {
        [$state, $consentRequest] = $this->resolve($request, $token);

        if ($state !== 'open') {
            return $this->status($state, $consentRequest, $token);
        }

        try {
            if ($request->validated('decision') === 'grant') {
                $this->parental->grant($consentRequest, $request->validated('guardian_name'), $request->validated('consents', []), $request);
            } else {
                $this->parental->decline($consentRequest, $request, $request->validated('reason'));
            }
        } catch (DomainException) {
            return $this->status('closed', $consentRequest->fresh(), $token);
        }

        return redirect()->to($request->fullUrl());
    }

    /**
     * Resend the guardian e-mail for a pending registration (same answer whatever the input,
     * so the form cannot be used to discover accounts).
     */
    public function resend(ParentalConsentResendRequest $request): RedirectResponse
    {
        $user = User::query()->where('email', Str::lower($request->validated('email')))->first();
        $pending = $user && ! $user->is_active ? ParentalConsentRequest::pendingFor($user) : null;

        if ($pending && $pending->isOpen()) {
            $this->parental->send($pending);
        }

        return back()->with('status', 'Jika ada pendaftaran yang menunggu persetujuan, email baru sudah kami kirim ke orang tua/wali. Tautan lama tidak berlaku lagi.');
    }

    /**
     * Resolve the token to a request and a state: open|granted|declined|expired|cancelled|invalid.
     *
     * @return array{0: string, 1: ParentalConsentRequest|null}
     */
    private function resolve(Request $request, string $token): array
    {
        $consentRequest = ParentalConsentRequest::findByToken($token);

        if (! $consentRequest) {
            return ['invalid', null];
        }

        $state = $consentRequest->effective_status;

        if ($state === 'pending') {
            // Reason: the signature binds the link to its expiry; a token alone is not enough to decide.
            return [$request->hasValidSignature() ? 'open' : 'invalid', $consentRequest];
        }

        return [$state, $consentRequest];
    }

    /** Status page for a closed request or an unusable link. */
    private function status(string $state, ?ParentalConsentRequest $consentRequest, string $token): Response
    {
        $inviteUrl = null;

        if ($state === 'granted' && $consentRequest && ! $consentRequest->guardian_user_id && setting('security.registration_enabled')
            && in_array('parent', (array) setting('security.registration_roles', []), true)) {
            $inviteUrl = route('register', ['peran' => 'parent', 'email' => $consentRequest->guardian_email, 'undangan' => $token]);
        }

        $code = match ($state) {
            'invalid' => 403,
            default => 200,
        };

        return response()->view('privacy.parental-consent-status', [
            'state' => $state,
            'consentRequest' => $consentRequest,
            'childFirstName' => $consentRequest?->child ? strtok((string) $consentRequest->child->name, ' ') : null,
            'inviteUrl' => $inviteUrl,
            'dpoEmail' => (string) setting('privacy.dpo_email', ''),
        ], $code);
    }
}
