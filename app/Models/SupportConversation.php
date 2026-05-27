<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_message_at',
        'last_user_message_at',
        'last_admin_message_at',
        'resolved_at',
        'unread_by_user_count',
        'unread_by_admin_count',
    ];

    protected $casts = [
        'last_message_at'       => 'datetime',
        'last_user_message_at'  => 'datetime',
        'last_admin_message_at' => 'datetime',
        'resolved_at'           => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('id');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->latest('id')->limit(1);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
