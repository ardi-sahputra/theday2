<?php

namespace App\Services;

use App\Mail\NewChatNotificationMail;
use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SupportConversationService
{
    public function findOrCreateForUser(User $user): SupportConversation
    {
        return SupportConversation::firstOrCreate(['user_id' => $user->id]);
    }

    public function sendUserMessage(SupportConversation $conv, ?string $body, ?UploadedFile $image): SupportMessage
    {
        $shouldEmail = $this->shouldNotifyEmail($conv);

        $msg = $this->insertMessage($conv, $conv->user, 'user', $body, $image);

        $conv->forceFill([
            'last_message_at'       => now(),
            'last_user_message_at'  => now(),
            'unread_by_admin_count' => $conv->unread_by_admin_count + 1,
            'resolved_at'           => null,
        ])->save();

        if ($shouldEmail) {
            Mail::to(config('support.notify_email'))
                ->queue(new NewChatNotificationMail($conv->fresh(['user']), $msg));
        }

        return $msg;
    }

    public function sendAdminMessage(SupportConversation $conv, Admin $admin, ?string $body, ?UploadedFile $image): SupportMessage
    {
        $msg = $this->insertMessage($conv, $admin, 'admin', $body, $image);

        $conv->forceFill([
            'last_message_at'       => now(),
            'last_admin_message_at' => now(),
            'unread_by_user_count'  => $conv->unread_by_user_count + 1,
        ])->save();

        return $msg;
    }

    public function shouldNotifyEmail(SupportConversation $conv): bool
    {
        $prev = $conv->last_user_message_at;
        if (!$prev) return true;
        return $prev->lt(now()->subDay());
    }

    private function insertMessage(SupportConversation $conv, $sender, string $role, ?string $body, ?UploadedFile $image): SupportMessage
    {
        $attachment = $image ? $this->storeImage($image) : null;

        return $conv->messages()->create([
            'sender_type'     => $sender::class,
            'sender_id'       => $sender->id,
            'sender_role'     => $role,
            'body'            => $body,
            'attachment_path' => $attachment['path'] ?? null,
            'attachment_mime' => $attachment['mime'] ?? null,
            'attachment_size' => $attachment['size'] ?? null,
        ]);
    }

    private function storeImage(UploadedFile $image): array
    {
        $path = $image->store('support/'.now()->format('Y/m'), config('filesystems.uploads'));
        return [
            'path' => $path,
            'mime' => $image->getMimeType(),
            'size' => $image->getSize(),
        ];
    }

    public function markReadByUser(SupportConversation $conv): void
    {
        $conv->messages()
            ->where('sender_role', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $conv->update(['unread_by_user_count' => 0]);
    }

    public function markReadByAdmin(SupportConversation $conv): void
    {
        $conv->messages()
            ->where('sender_role', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $conv->update(['unread_by_admin_count' => 0]);
    }

    public function resolve(SupportConversation $conv): void
    {
        $conv->update(['resolved_at' => now()]);
    }

    public function isWithinWorkHours(): bool
    {
        $hours = AdminSetting::get('support_work_hours', [
            'timezone' => 'Asia/Jakarta',
            'days'     => [1, 2, 3, 4, 5, 6],
            'start'    => '09:00',
            'end'      => '18:00',
        ]);

        $now = Carbon::now($hours['timezone']);
        $dayOfWeek = $now->dayOfWeekIso;

        if (!in_array($dayOfWeek, $hours['days'])) {
            return false;
        }

        $current = $now->format('H:i');
        return $current >= $hours['start'] && $current < $hours['end'];
    }

    public function adminOnlineStatus(): array
    {
        return [
            'online'          => false,
            'work_hours_open' => $this->isWithinWorkHours(),
        ];
    }
}
