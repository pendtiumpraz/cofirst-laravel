<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified (signed link from the email).
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $target = route('dashboard', absolute: false).'?verified=1';

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($target)->with('info', 'Email Anda sudah terverifikasi sebelumnya.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($target)->with('success', 'Email Anda berhasil diverifikasi.');
    }
}
