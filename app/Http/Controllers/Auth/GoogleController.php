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
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(
        AssignFreeSubscriptionAction $assignFreeSubscription,
        CreateInvitationFromTemplateAction $createInvitation,
    ): RedirectResponse {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $updates = [];
            if (! $user->google_id) $updates['google_id'] = $googleUser->getId();
            if (! $user->email_verified_at) $updates['email_verified_at'] = now();
            if ($updates) $user->update($updates);
        } else {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar_url'        => $googleUser->getAvatar(),
                'email_verified_at' => now(),
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
                return redirect()->route('dashboard.invitations.edit', $invitation)
                    ->with('flash', ['type' => 'success', 'message' => 'Selamat datang! Template sudah dipilih.']);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
