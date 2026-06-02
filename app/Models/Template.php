<?php

// app/Models/Template.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TemplateTier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'thumbnail_url',
        'description',
        'default_config',
        'demo_data',
        'tier',
        'is_active',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'default_config' => 'array',
            'demo_data'      => 'array',
            'tier'           => TemplateTier::class,
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
            'is_featured'    => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(TemplateAsset::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('tier', TemplateTier::Free);
    }

    public function scopePremium(Builder $query): Builder
    {
        return $query->where('tier', TemplateTier::Premium);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isFree(): bool
    {
        return $this->tier === TemplateTier::Free;
    }

    public function isPremium(): bool
    {
        return $this->tier === TemplateTier::Premium;
    }
}
