<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Handle the email verification link.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
{
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->intended(route('dashboard.customer'))->with('success', '🎉 Your email was already verified.');
    }

    if ($request->user()->markEmailAsVerified()) {
        event(new Verified($request->user()));
    }

    return redirect()->intended(route('dashboard.customer'))->with('success', '🎉 Your email has been successfully verified!');
}

}
