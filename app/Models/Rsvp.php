<?php

// app/Models/Rsvp.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invitation_id',
        'guest_name',
        'phone',
        'attendance',
        'guest_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance'  => AttendanceStatus::class,
            'guest_count' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeHadir(Builder $query): Builder
    {
        return $query->where('attendance', AttendanceStatus::Hadir);
    }

    public function scopeTidakHadir(Builder $query): Builder
    {
        return $query->where('attendance', AttendanceStatus::TidakHadir);
    }

    public function scopeByType(Builder $query, AttendanceStatus $status): Builder
    {
        return $query->where('attendance', $status);
    }
}
