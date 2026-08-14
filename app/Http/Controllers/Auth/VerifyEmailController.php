<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Handle the email verification link.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        $user = Auth::user() ?? User::find($id);

        if (! $user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            if (! Auth::check()) {
                Auth::login($user);
            }
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1')
                ->with('success', '🎉 Your email was already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if (! Auth::check()) {
            Auth::login($user);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1')
            ->with('success', '🎉 Your email has been successfully verified!');
    }
}

