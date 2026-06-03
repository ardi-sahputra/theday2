<?php

// app/Http/Controllers/Auth/GoogleController.php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\AssignFreeSubscriptionAction;
use App\Actions\CreateInvitationFromTemplateAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        // Stateful: Socialite stores a CSRF `state` nonce in the session and
        // verifies it on callback, preventing OAuth login-CSRF.
        return Socialite::driver('google')->redirect();
    }

    public function callback(
        AssignFreeSubscriptionAction $assignFreeSubscription,
        CreateInvitationFromTemplateAction $createInvitation,
    ): RedirectResponse {
        $googleUser = Socialite::driver('google')->user();

        // Did Google assert this email is verified? (OIDC `email_verified`.)
        $emailVerified = (bool) ($googleUser->user['email_verified']
            ?? $googleUser->user['verified_email']
            ?? false);

        // Match an existing identity by Google id first.
        $user = User::where('google_id', $googleUser->getId())->first();

        // Fall back to email — but only auto-link to a pre-existing (possibly
        // password-only) account when Google has VERIFIED the email. Otherwise an
        // attacker controlling an unverified-email Google account could take over.
        if (! $user) {
            $existing = User::where('email', $googleUser->getEmail())->first();
            if ($existing) {
                if (! $emailVerified) {
                    return redirect('/login')->withErrors([
                        'email' => 'Email Google ini belum terverifikasi. Masuk dengan password, lalu hubungkan akun Google dari profil.',
                    ]);
                }
                $user = $existing;
            }
        }

        if ($user) {
            $updates = [];
            if (! $user->google_id) $updates['google_id'] = $googleUser->getId();
            if (! $user->email_verified_at && $emailVerified) $updates['email_verified_at'] = now();
            if ($updates) $user->update($updates);
        } else {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar_url'        => $googleUser->getAvatar(),
                'email_verified_at' => $emailVerified ? now() : null,
                'locale'            => app()->getLocale(),
            ]);

            $assignFreeSubscription->execute($user);
            event(new Registered($user));
        }

        Auth::login($user, remember: true);

        if (! $user->hasCompletedOnboarding()) {
            return redirect()->route('onboarding');
        }

        $pendingTemplate = session()->pull('pending_template');

        if ($pendingTemplate) {
            $invitation = $createInvitation->execute($user, $pendingTemplate);
            if ($invitation) {
                return redirect()->route('dashboard.invitations.customize-v2', $invitation)
                    ->with('flash', ['type' => 'success', 'message' => 'Selamat datang! Template sudah dipilih.']);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
