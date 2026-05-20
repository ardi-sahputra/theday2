<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'groom_name',
        'groom_nickname',
        'groom_instagram',
        'groom_parent_names',
        'bride_name',
        'bride_nickname',
        'bride_instagram',
        'bride_parent_names',
        'wedding_date',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
