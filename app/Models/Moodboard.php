<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Moodboard extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'concept_note',
        'palette',
    ];

    protected $casts = [
        'palette' => 'array',
    ];

    protected $attributes = [
        'name' => 'Moodboard Pernikahan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MoodboardItem::class)->orderBy('sort_order');
    }
}
