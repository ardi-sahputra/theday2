<?php

// app/Models/WeddingPlan.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeddingPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'primary_invitation_id',
        'event_date',
        'checklist_initialized_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date'               => 'date',
            'checklist_initialized_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryInvitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class, 'primary_invitation_id');
    }

    public function checklistTasks(): HasMany
    {
        return $this->hasMany(ChecklistTask::class);
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isChecklistInitialized(): bool
    {
        return $this->checklist_initialized_at !== null;
    }

    public function hasEventDate(): bool
    {
        return $this->event_date !== null;
    }
}
