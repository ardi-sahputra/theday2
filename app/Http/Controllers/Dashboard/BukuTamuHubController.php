<?php

// app/Http/Controllers/Dashboard/BukuTamuHubController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Support\EffectiveUser;
use Inertia\Inertia;
use Inertia\Response;

class BukuTamuHubController extends Controller
{
    public function index(): Response
    {
        $userId = EffectiveUser::resolve()->id;

        $invitations = Invitation::where('user_id', $userId)
            ->with('template:id,name,default_config')
            ->withCount(['guestMessages as total_messages'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inv) {
                $base = GuestMessage::where('invitation_id', $inv->id);

                return [
                    'id'             => $inv->id,
                    'title'          => $inv->title,
                    'slug'           => $inv->slug,
                    'status'         => $inv->status->value,
                    'total_messages' => $inv->total_messages,
                    'visible_count'  => (clone $base)->visible()->count(),
                    'hidden_count'   => (clone $base)->hidden()->count(),
                    'pinned_count'   => (clone $base)->pinned()->count(),
                    'template_color' => $inv->template->default_config['primary_color'] ?? '#D4A373',
                ];
            });

        return Inertia::render('Dashboard/BukuTamu/Index', [
            'invitations' => $invitations,
        ]);
    }
}
