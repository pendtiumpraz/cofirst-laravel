<?php

namespace App\Http\Controllers\Privacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Privacy\CookieConsentRequest;
use App\Models\Privacy\ConsentRecord;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\CookieConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Cookie preferences page and the endpoint the banner posts to
 * (UU 27/2022 Pasal 20 ayat (2) huruf a & 22; withdrawal as easy as consent, Pasal 9).
 */
class CookieConsentController extends Controller
{
    public function __construct(
        private readonly CookieConsentService $cookies,
        private readonly ConsentService $consents,
    ) {
    }

    /**
     * Show the cookie preferences form (works without JavaScript).
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $keys = array_values(CookieConsentService::PURPOSES);

        return view('privacy.cookie-preferences', [
            'state' => $this->cookies->state($request, $user),
            'purposes' => collect(CookieConsentService::PURPOSES)->map(fn ($key) => $this->consents->purpose($key)),
            'history' => $user
                ? ConsentRecord::query()->where('subject_user_id', $user->id)->whereIn('purpose_key', $keys)->latest('id')->limit(6)->get()
                : collect(),
        ]);
    }

    /**
     * Store a choice: first-party cookie for everyone, consent ledger for signed-in accounts.
     */
    public function update(CookieConsentRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $minor = $user ? $this->consents->isChild($user) : false;
        $choices = $this->cookies->choices(
            $request->validated('action'),
            $request->boolean('functional'),
            $request->boolean('analytics'),
            $minor,
        );

        if ($user) {
            $this->cookies->record($user, $choices, $request, 'banner', $request->validated('source') ?? 'banner');
        }

        $cookie = $this->cookies->makeCookie($choices, $request->isSecure());

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'choices' => $choices])->withCookie($cookie);
        }

        return back()->with('success', 'Preferensi cookie Anda tersimpan.')->withCookie($cookie);
    }
}
