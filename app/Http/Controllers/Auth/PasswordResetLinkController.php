<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Neutral confirmation shown whatever the outcome.
     *
     * Reason: telling "email not found" apart from "link sent" lets anyone test which
     * emails (of students and parents) are registered — account enumeration.
     */
    public const NEUTRAL_STATUS = 'Jika email tersebut terdaftar, kami telah mengirim tautan untuk membuat kata sandi baru. Periksa kotak masuk dan folder spam Anda.';

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link when the email belongs to an account.
     */
    public function store(PasswordResetLinkRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire', 60);

        return back()->with('status', self::NEUTRAL_STATUS." Tautan berlaku {$minutes} menit.");
    }
}
