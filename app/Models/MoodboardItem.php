<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodboardItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'moodboard_id',
        'image_path',
        'image_url',
        'caption',
        'tag',
        'colors',
        'sort_order',
    ];

    protected $casts = [
        'colors'     => 'array',
        'sort_order' => 'integer',
    ];

    public function moodboard(): BelongsTo
    {
        return $this->belongsTo(Moodboard::class);
    }
}
