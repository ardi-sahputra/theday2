<?php

// app/Models/Plan.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration_days',
        'max_invitations',
        'max_gallery_photos',
        'custom_music',
        'remove_watermark',
        'custom_domain',
        'analytics_access',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'duration_days'      => 'integer',
            'max_invitations'    => 'integer',
            'max_gallery_photos' => 'integer',
            'custom_music'       => 'boolean',
            'remove_watermark'   => 'boolean',
            'custom_domain'      => 'boolean',
            'analytics_access'   => 'boolean',
            'features'           => 'array',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(PlanDiscount::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isFree(): bool
    {
        return (float) $this->price === 0.0;
    }

    public function isPremiumFeature(string $feature): bool
    {
        return match($feature) {
            'custom_music'     => $this->custom_music,
            'remove_watermark' => $this->remove_watermark,
            'custom_domain'    => $this->custom_domain,
            'analytics_access' => $this->analytics_access,
            default            => false,
        };
    }

    public function currentDiscount(): ?PlanDiscount
    {
        return $this->discounts()->active()->first();
    }

    public function hasActiveDiscount(): bool
    {
        return $this->discounts()->active()->exists();
    }

    public function effectivePrice(): int
    {
        $discount = $this->currentDiscount();
        if ($discount === null) {
            return (int) $this->price;
        }
        return (int) round((int) $this->price * (1 - $discount->percent / 100));
    }
}
