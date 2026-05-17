<?php

// app/Http/Controllers/Auth/AuthenticatedSessionController.php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\CreateInvitationFromTemplateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    public function store(
        LoginRequest $request,
        CreateInvitationFromTemplateAction $createInvitation,
    ): RedirectResponse|JsonResponse {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Onboarding not done — go to onboarding (pending_template is kept in session)
        if (! $user->hasCompletedOnboarding()) {
            $url = route('onboarding');
            if ($request->wantsJson()) {
                return response()->json(['redirect' => $url]);
            }
            return redirect($url);
        }

        // Onboarding done + has pending template → create/update invitation
        $pendingTemplate = session()->pull('pending_template');

        if ($pendingTemplate) {
            $invitation = $createInvitation->execute($user, $pendingTemplate);

            if ($invitation) {
                $url = route('dashboard.invitations.edit', $invitation);
                if ($request->wantsJson()) {
                    return response()->json(['redirect' => $url]);
                }
                return redirect($url)->with('flash', ['type' => 'success', 'message' => 'Selamat datang kembali! Template sudah dipilih.']);
            }
        }

        $fallback = route('dashboard', absolute: false);
        if ($request->wantsJson()) {
            return response()->json(['redirect' => $fallback]);
        }
        return redirect()->intended($fallback);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
