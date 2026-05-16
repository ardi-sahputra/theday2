<?php

// app/Http/Controllers/Auth/RegisteredUserController.php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\AssignFreeSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(
        Request $request,
        AssignFreeSubscriptionAction $assignFreeSubscription,
    ): RedirectResponse|JsonResponse {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'name.max'          => 'Nama terlalu panjang, maks. 255 karakter.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.lowercase'   => 'Email harus ditulis dengan huruf kecil.',
            'email.unique'      => 'Email ini sudah terdaftar. Coba masuk atau gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $assignFreeSubscription->execute($user);

        event(new Registered($user));

        Auth::login($user);

        $url = route('verification.notice');
        if ($request->wantsJson()) {
            return response()->json(['redirect' => $url]);
        }
        return redirect($url);
    }
}
