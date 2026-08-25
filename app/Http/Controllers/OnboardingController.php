<?php

// app/Http/Controllers/OnboardingController.php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Template;
use App\Support\InvitationSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
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
                ->with('discounts')
                ->get()
                ->map(fn ($p) => [
                    'slug'             => $p->slug,
                    'name'             => $p->name,
                    'price'            => (int) $p->price,
                    'effective_price'  => $p->effectivePrice(),
                    'discount_percent' => $p->currentDiscount()?->percent,
                ])
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

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Update user phone if provided and changed
        if (! empty($data['phone']) && $data['phone'] !== $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }

        // Persist couple identity to the durable profile for EVERY user
        // (preparing or married). This is the source of truth for the couple's
        // names, so any invitation created later — even after the onboarding
        // session is gone — can pre-fill them. See CreateInvitationFromTemplateAction.
        \App\Models\CoupleProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'groom_name'     => $data['groom_name'],
                'groom_nickname' => $data['groom_nickname'] ?? null,
                'bride_name'     => $data['bride_name'],
                'bride_nickname' => $data['bride_nickname'] ?? null,
                'wedding_date'   => $noDate ? null : ($data['wedding_date'] ?? null),
            ],
        );

        $isMarried = ($data['marital_status'] ?? null) === 'sudah';

        if ($isMarried) {
            // Already married → no wedding invitation to send; the profile above
            // is the anniversary foundation. Event invitations can come later.
            $needsDesign = false;
        } else {
            // Preparing for the wedding. Only create the invitation now if a
            // design was already chosen (picked before registering). Otherwise
            // stash the couple data and let them pick a template first.
            $pendingTemplateId = session()->pull('pending_template');
            $template = $pendingTemplateId ? Template::find($pendingTemplateId) : null;

            if ($template) {
                $invitation = Invitation::create([
                    'user_id'    => $user->id,
                    'template_id' => $template->id,
                    'title'       => trim("{$data['groom_name']} & {$data['bride_name']}"),
                    'event_type'  => 'pernikahan',
                    'marital_status' => $data['marital_status'] ?? null,
                    'wedding_type' => $data['wedding_type'] ?? null,
                    'city'         => $data['city'] ?? null,
                    'intended_plan' => $data['intended_plan'] ?? null,
                    'slug'        => InvitationSlug::forCouple(
                        $data['groom_nickname'] ?? $data['groom_name'],
                        $data['bride_nickname'] ?? $data['bride_name'],
                    ),
                    'status'      => 'draft',
                ]);

                $invitation->details()->create([
                    'invitation_id'  => $invitation->id,
                    'groom_name'     => $data['groom_name'],
                    'groom_nickname' => $data['groom_nickname'] ?? null,
                    'bride_name'     => $data['bride_name'],
                    'bride_nickname' => $data['bride_nickname'] ?? null,
                ]);

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

                $needsDesign = false;
            } else {
                // No design chosen → remember the couple data so it pre-fills the
                // invitation once they pick a template from the gallery.
                session(['pending_couple_data' => [
                    'groom_name'     => $data['groom_name'],
                    'groom_nickname' => $data['groom_nickname'] ?? null,
                    'bride_name'     => $data['bride_name'],
                    'bride_nickname' => $data['bride_nickname'] ?? null,
                    'wedding_date'   => $noDate ? null : ($data['wedding_date'] ?? null),
                    'start_time'     => $data['start_time'] ?? null,
                    'venue_name'     => $data['venue_name'] ?? null,
                    'venue_address'  => $data['venue_address'] ?? null,
                    'wedding_type'   => $data['wedding_type'] ?? null,
                    'city'           => $data['city'] ?? null,
                    'intended_plan'  => $data['intended_plan'] ?? null,
                ]]);
                $needsDesign = true;
            }
        }

        // Mark onboarding as complete
        $user->update(['onboarding_completed_at' => now()]);

        // Routing: Premium intent → checkout. Everyone else → dashboard, where
        // the next-action hero guides them (pick a design if none chosen yet).
        if (($data['intended_plan'] ?? null) === 'premium') {
            return redirect()->route('dashboard.paket', ['checkout' => 'premium'])
                ->with('flash', ['type' => 'success', 'message' => 'Setup selesai — lanjut pilih paket Premium.']);
        }

        return redirect()->route('dashboard')->with('flash', [
            'type'    => 'success',
            'message' => $isMarried
                ? 'Selamat! Profil pasangan kalian tersimpan.'
                : ($needsDesign
                    ? 'Sip! Tinggal pilih desain undangan kalian dari dashboard.'
                    : 'Selamat! Setup selesai. Undanganmu siap dikustomisasi.'),
        ]);
    }
}
