<?php

// app/Models/ChecklistTask.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChecklistTaskCategory;
use App\Enums\ChecklistTaskPriority;
use App\Enums\ChecklistTaskSource;
use App\Enums\ChecklistTaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistTask extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'wedding_plan_id',
        'invitation_id',
        'source',
        'template_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'due_date',
        'assignee_type',
        'assignee_label',
        'reminder_enabled',
        'reminder_offset_days',
        'last_reminded_at',
        'next_reminder_at',
        'sort_order',
        'is_user_modified',
        'completed_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'source'               => ChecklistTaskSource::class,
            'category'             => ChecklistTaskCategory::class,
            'priority'             => ChecklistTaskPriority::class,
            'status'               => ChecklistTaskStatus::class,
            'due_date'             => 'date',
            'reminder_enabled'     => 'boolean',
            'reminder_offset_days' => 'integer',
            'last_reminded_at'     => 'datetime',
            'next_reminder_at'     => 'datetime',
            'is_user_modified'     => 'boolean',
            'completed_at'         => 'datetime',
            'archived_at'          => 'datetime',
            'sort_order'           => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function weddingPlan(): BelongsTo
    {
        return $this->belongsTo(WeddingPlan::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ChecklistSubtask::class, 'task_id')->orderBy('sort_order')->orderBy('created_at');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [ChecklistTaskStatus::Todo, ChecklistTaskStatus::Done]);
    }

    public function scopeTodo(Builder $query): Builder
    {
        return $query->where('status', ChecklistTaskStatus::Todo);
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', ChecklistTaskStatus::Done);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', ChecklistTaskStatus::Archived);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSystemUnmodified(Builder $query): Builder
    {
        return $query->where('source', ChecklistTaskSource::System)
                     ->where('is_user_modified', false);
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isArchived(): bool
    {
        return $this->status === ChecklistTaskStatus::Archived;
    }

    public function isDone(): bool
    {
        return $this->status === ChecklistTaskStatus::Done;
    }

    public function isSystemTask(): bool
    {
        return $this->source === ChecklistTaskSource::System;
    }

    public function canToggle(): bool
    {
        return ! $this->isArchived();
    }

    public function canArchive(): bool
    {
        return ! $this->isArchived();
    }

    public function canRestore(): bool
    {
        return $this->isArchived();
    }
}
