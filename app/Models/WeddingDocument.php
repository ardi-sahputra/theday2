<?php

// app/Models/WeddingDocument.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WeddingDocumentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'wedding_plan_id',
        'key',
        'status',
        'file_path',
        'note',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => WeddingDocumentStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function weddingPlan(): BelongsTo
    {
        return $this->belongsTo(WeddingPlan::class);
    }
}
