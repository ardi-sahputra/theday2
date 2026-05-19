<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupportConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'user'                   => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'last_message_at'        => $this->last_message_at?->toIso8601String(),
            'last_user_message_at'   => $this->last_user_message_at?->toIso8601String(),
            'last_admin_message_at'  => $this->last_admin_message_at?->toIso8601String(),
            'resolved_at'            => $this->resolved_at?->toIso8601String(),
            'unread_by_user_count'   => $this->unread_by_user_count,
            'unread_by_admin_count'  => $this->unread_by_admin_count,
            'latest_message'         => SupportMessageResource::make($this->whenLoaded('latestMessage')->first()),
        ];
    }
}
