<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_conversation_id',
        'sender_type',
        'sender_id',
        'sender_role',
        'body',
        'attachment_path',
        'attachment_mime',
        'attachment_size',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachmentUrl(): ?string
    {
        return $this->attachment_path
            ? Storage::disk('public')->url($this->attachment_path)
            : null;
    }
}
