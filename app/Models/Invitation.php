<?php

// app/Models/Invitation.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventType;
use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'template_id',
        'slug',
        'title',
        'event_type',
        'custom_config',
        'status',
        'published_at',
        'expires_at',
        'is_password_protected',
        'password',
        'view_count',
        'current_step',
        'last_edited_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'event_type'           => EventType::class,
            'status'               => InvitationStatus::class,
            'custom_config'        => 'array',
            'published_at'         => 'datetime',
            'expires_at'           => 'datetime',
            'last_edited_at'       => 'datetime',
            'is_password_protected' => 'boolean',
            'view_count'           => 'integer',
            'current_step'         => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function details(): HasOne
    {
        return $this->hasOne(InvitationDetail::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(InvitationEvent::class)->orderBy('sort_order')->orderBy('event_date');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(InvitationGallery::class)->orderBy('sort_order');
    }

    public function music(): HasMany
    {
        return $this->hasMany(InvitationMusic::class)->orderBy('sort_order');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function guestMessages(): HasMany
    {
        return $this->hasMany(GuestMessage::class)->latest();
    }

    public function views(): HasMany
    {
        return $this->hasMany(InvitationView::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(InvitationSection::class)->orderBy('sort_order');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::Draft);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::Published);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::Archived);
    }

    public function scopeByUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType(Builder $query, EventType $type): Builder
    {
        return $query->where('event_type', $type);
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isArchived(): bool
    {
        return $this->status === InvitationStatus::Archived;
    }

    public function isOwner(User $user): bool
    {
        // Resolve effective user so a partner is treated as owning the
        // owner's invitations when authed as partner.
        $effectiveId = \App\Support\EffectiveUser::resolve()?->id ?? $user->id;

        return $this->user_id === $effectiveId;
    }

    public function isPublished(): bool
    {
        return $this->status === InvitationStatus::Published;
    }
}
