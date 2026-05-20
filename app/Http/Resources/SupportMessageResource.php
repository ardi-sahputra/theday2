<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SupportMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'sender_role'     => $this->sender_role,
            'body'            => $this->body,
            'attachment_url'  => $this->attachment_path
                ? Storage::disk(config('filesystems.uploads'))->url($this->attachment_path)
                : null,
            'attachment_mime' => $this->attachment_mime,
            'attachment_size' => $this->attachment_size,
            'read_at'         => $this->read_at?->toIso8601String(),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
