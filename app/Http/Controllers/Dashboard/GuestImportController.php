<?php

// app/Http/Controllers/Dashboard/GuestImportController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\GuestList;
use App\Models\User;
use App\Services\GuestSlugGenerator;
use App\Services\Notifications\NotificationPublisher;
use App\Services\PhoneNumberNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuestImportController extends Controller
{
    public function __construct(
        private readonly PhoneNumberNormalizer $normalizer,
        private readonly GuestSlugGenerator    $slugGenerator,
        private readonly NotificationPublisher $notificationPublisher,
    ) {}

    // ─── Preview ──────────────────────────────────────────────────

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'mode'          => 'required|in:paste,csv',
            'invitation_id' => 'nullable|exists:invitations,id',
            'raw_text'      => 'required_if:mode,paste|nullable|string',
            'file'          => 'required_if:mode,csv|nullable|file|mimes:csv,txt|max:2048',
        ]);

        $rows = $request->input('mode') === 'paste'
            ? $this->parsePaste($request->input('raw_text', ''))
            : $this->parseCsv($request->file('file'));

        $preview = $rows->map(function ($row, $index) {
            $errors          = [];
            $normalizedPhone = null;

            if (empty(trim($row['name'] ?? ''))) {
                $errors[] = 'Nama tidak boleh kosong';
            }

            if (empty(trim($row['phone'] ?? ''))) {
                $errors[] = 'Nomor WhatsApp tidak boleh kosong';
            } elseif (! $this->normalizer->isValid($row['phone'])) {
                $errors[] = 'Nomor WhatsApp tidak valid';
            } else {
                $normalizedPhone = $this->normalizer->normalize($row['phone']);
            }

            return [
                'row'              => $index + 1,
                'name'             => trim($row['name'] ?? ''),
                'phone'            => trim($row['phone'] ?? ''),
                'category'         => trim($row['category'] ?? ''),
                'greeting'         => trim($row['greeting'] ?? ''),
                'note'             => trim($row['note'] ?? ''),
                'normalized_phone' => $normalizedPhone,
                'valid'            => empty($errors),
                'errors'           => $errors,
            ];
        })->values();

        return response()->json([
            'rows'          => $preview,
            'valid_count'   => $preview->where('valid', true)->count(),
            'invalid_count' => $preview->where('valid', false)->count(),
        ]);
    }

    // ─── Commit ───────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'invitation_id' => 'nullable|exists:invitations,id',
            'rows'          => 'required|array|min:1|max:500',
            'rows.*.name'   => 'required|string|max:150',
            'rows.*.phone'  => 'required|string|max:30',
            'rows.*.category' => 'nullable|string|max:50',
            'rows.*.greeting' => 'nullable|string|max:50',
            'rows.*.note'     => 'nullable|string|max:500',
        ]);

        $userId       = Auth::id();
        $invitationId = $request->input('invitation_id');
        $imported     = 0;
        $skipped      = 0;

        DB::transaction(function () use ($request, $userId, $invitationId, &$imported, &$skipped) {
            foreach ($request->input('rows') as $row) {
                if (! $this->normalizer->isValid($row['phone'])) {
                    $skipped++;
                    continue;
                }

                $normalized = $this->normalizer->normalize($row['phone']);
                $slug       = $this->slugGenerator->generate(
                    $row['name'],
                    $invitationId,
                    $userId
                );

                GuestList::create([
                    'user_id'          => $userId,
                    'invitation_id'    => $invitationId,
                    'name'             => trim($row['name']),
                    'phone_number'     => trim($row['phone']),
                    'normalized_phone' => $normalized,
                    'category'         => trim($row['category'] ?? '') ?: null,
                    'greeting'         => trim($row['greeting'] ?? '') ?: null,
                    'note'             => trim($row['note'] ?? '') ?: null,
                    'guest_slug'       => $slug,
                ]);

                $imported++;
            }
        });

        if ($imported > 0) {
            $user = User::find($userId);
            if ($user !== null) {
                $this->notificationPublisher->publish(
                    user: $user,
                    type: NotificationType::GuestImportCompleted,
                    payload: ['imported' => $imported],
                    actionUrl: $invitationId ? '/dashboard/invitations/' . $invitationId . '/buku-tamu' : '/dashboard/buku-tamu',
                );
            }
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'message'  => "{$imported} tamu berhasil diimport." . ($skipped > 0 ? " {$skipped} baris dilewati." : ''),
        ]);
    }

    // ─── Parsers ──────────────────────────────────────────────────

    private function parsePaste(string $text): Collection
    {
        return collect(explode("\n", trim($text)))
            ->filter(fn ($line) => trim($line) !== '')
            ->map(function ($line) {
                $parts = str_getcsv(trim($line));

                return [
                    'name'     => $parts[0] ?? '',
                    'phone'    => $parts[1] ?? '',
                    'category' => $parts[2] ?? '',
                    'greeting' => $parts[3] ?? '',
                    'note'     => $parts[4] ?? '',
                ];
            })
            ->values();
    }

    private function parseCsv($file): Collection
    {
        $rows   = collect();
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            return $rows;
        }

        $headers = null;

        while (($data = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($h) => strtolower(trim($h)), $data);
                continue;
            }

            $combined = array_combine($headers, array_pad($data, count($headers), ''));

            $rows->push([
                'name'     => $combined['name']     ?? $combined['nama']    ?? '',
                'phone'    => $combined['phone']    ?? $combined['telepon'] ?? $combined['no'] ?? '',
                'category' => $combined['category'] ?? $combined['kategori'] ?? '',
                'greeting' => $combined['greeting'] ?? $combined['sapaan']  ?? '',
                'note'     => $combined['note']     ?? $combined['catatan'] ?? '',
            ]);
        }

        fclose($handle);

        return $rows;
    }
}
