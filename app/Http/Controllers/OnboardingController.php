<?php

// app/Http/Controllers/OnboardingController.php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /** Reserved slugs that cannot be used as invitation URLs */
    private const RESERVED_SLUGS = [
        'login', 'register', 'logout', 'dashboard', 'admin', 'templates',
        'editor', 'use-template', 'onboarding', 'profile', 'up',
        'verify-email', 'confirm-password', 'forgot-password',
        'reset-password', 'email', 'sitemap', 'api',
    ];

    public function show(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Setup', [
            'user' => [
                'name'  => $request->user()->name,
                'phone' => $request->user()->phone,
            ],
            'plans' => \App\Models\Plan::whereIn('slug', ['free', 'premium'])
                ->orderBy('sort_order')
                ->get(['slug', 'name', 'price'])
                ->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        $noDate = $request->boolean('no_date');

        $data = $request->validate([
            'groom_name'     => ['required', 'string', 'max:255'],
            'groom_nickname' => ['nullable', 'string', 'max:10'],
            'bride_name'     => ['required', 'string', 'max:255'],
            'bride_nickname' => ['nullable', 'string', 'max:10'],
            'phone'          => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'no_date'        => ['boolean'],
            'wedding_date'   => [$noDate ? 'nullable' : 'required', 'nullable', 'date_format:Y-m-d'],
            'start_time'     => ['nullable', 'date_format:H:i'],
            'venue_name'     => ['nullable', 'string', 'max:255'],
            'venue_address'  => ['nullable', 'string', 'max:1000'],
            'marital_status' => ['nullable', 'string', 'in:belum,sudah'],
            'wedding_type'   => ['nullable', 'string', 'in:akad-resepsi,intimate,destination,belum'],
            'city'           => ['nullable', 'string', 'max:120'],
            'intended_plan'  => ['nullable', 'string', 'in:free,premium'],
        ], [
            'groom_name.required'   => 'Nama mempelai pria wajib diisi.',
            'bride_name.required'   => 'Nama mempelai wanita wajib diisi.',
            'groom_nickname.max'    => 'Nama panggilan pria maksimal 10 karakter.',
            'bride_nickname.max'    => 'Nama panggilan wanita maksimal 10 karakter.',
            'wedding_date.required' => 'Tanggal pernikahan wajib diisi, atau centang "Belum menentukan tanggal".',
        ]);

        $user = $request->user();

        // Update user phone if provided and changed
        if (! empty($data['phone']) && $data['phone'] !== $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }

        // Resolve template: use pending session or fall back to first active free template
        $pendingTemplateId = session()->pull('pending_template');
        $template = $pendingTemplateId
            ? Template::find($pendingTemplateId)
            : Template::active()->where('tier', 'free')->ordered()->first();

        // Build title and auto-generate slug
        $title = trim("{$data['groom_name']} & {$data['bride_name']}");
        $slug  = $this->generateUniqueSlug($data);

        // Create the first invitation draft
        $invitation = Invitation::create([
            'user_id'    => $user->id,
            'template_id' => $template->id,
            'title'       => $title,
            'event_type'  => 'pernikahan',
            'marital_status' => $data['marital_status'] ?? null,
            'wedding_type' => $data['wedding_type'] ?? null,
            'city'         => $data['city'] ?? null,
            'intended_plan' => $data['intended_plan'] ?? null,
            'slug'        => $slug,
            'status'      => 'draft',
        ]);

        // Create invitation details with couple data
        $invitation->details()->create([
            'invitation_id'  => $invitation->id,
            'groom_name'     => $data['groom_name'],
            'groom_nickname' => $data['groom_nickname'] ?? null,
            'bride_name'     => $data['bride_name'],
            'bride_nickname' => $data['bride_nickname'] ?? null,
        ]);

        // Create event if date is provided
        if (! $noDate && ! empty($data['wedding_date'])) {
            $invitation->events()->create([
                'event_name'    => 'Akad & Resepsi',
                'event_date'    => $data['wedding_date'],
                'start_time'    => $data['start_time'] ?? null,
                'venue_name'    => $data['venue_name'] ?? '',
                'venue_address' => $data['venue_address'] ?? null,
                'sort_order'    => 0,
            ]);
        }

        // Mark onboarding as complete
        $user->update(['onboarding_completed_at' => now()]);

        return redirect()
            ->route('dashboard')
            ->with('flash', [
                'type'    => 'success',
                'message' => 'Selamat! Setup selesai. Undanganmu siap dikustomisasi.',
            ]);
    }

    private function generateUniqueSlug(array $data): string
    {
        $bride = Str::slug($data['bride_nickname'] ?? explode(' ', $data['bride_name'])[0]);
        $groom = Str::slug($data['groom_nickname'] ?? explode(' ', $data['groom_name'])[0]);
        $base  = ($bride && $groom) ? "{$bride}-{$groom}" : ($bride ?: ($groom ?: 'undangan'));

        $slug = $base;
        $i    = 1;

        while (
            Invitation::withTrashed()->where('slug', $slug)->exists()
            || in_array($slug, self::RESERVED_SLUGS, true)
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
