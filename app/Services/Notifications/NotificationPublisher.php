<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationPublisher
{
    public function __construct(
        private readonly NotificationRenderer $renderer,
    ) {
    }

    /**
     * Publish a notification for a user. Returns the resulting notification or null
     * (preference disabled, error, or cooldown skip with no row created).
     *
     * @param array<string,scalar|null> $payload
     */
    public function publish(
        User $user,
        NotificationType $type,
        array $payload = [],
        ?string $groupKey = null,
        ?string $actionUrl = null,
        ?int $cooldownDays = null,
    ): ?UserNotification {
        $category = $type->category();

        if (! $this->isCategoryEnabled($user, $category)) {
            return null;
        }

        try {
            return DB::transaction(function () use ($user, $type, $category, $payload, $groupKey, $actionUrl, $cooldownDays) {
                $locale = $user->locale ?? app()->getLocale();

                if ($groupKey !== null) {
                    $existing = UserNotification::query()
                        ->where('user_id', $user->id)
                        ->where('group_key', $groupKey)
                        ->whereNull('read_at')
                        ->lockForUpdate()
                        ->first();

                    if ($existing !== null) {
                        if ($cooldownDays !== null && $existing->updated_at->gt(now()->subDays($cooldownDays))) {
                            // Inside cooldown window — do not bump.
                            return $existing;
                        }

                        $newCount = $existing->count + 1;
                        $existing->update([
                            'count'        => $newCount,
                            'title'        => $this->renderer->render($type, $payload, $newCount, $locale),
                            'payload'      => array_merge($existing->payload ?? [], $payload),
                            'action_url'   => $actionUrl ?? $existing->action_url,
                            'template_key' => $type->translationKey(),
                            'locale'       => $locale,
                        ]);
                        $existing->touch();

                        return $existing;
                    }
                }

                return UserNotification::create([
                    'user_id'      => $user->id,
                    'category'     => $category->value,
                    'type'         => $type->value,
                    'group_key'    => $groupKey,
                    'count'        => 1,
                    'title'        => $this->renderer->render($type, $payload, 1, $locale),
                    'payload'      => $payload,
                    'action_url'   => $actionUrl,
                    'template_key' => $type->translationKey(),
                    'locale'       => $locale,
                ]);
            });
        } catch (Throwable $e) {
            Log::warning('NotificationPublisher failed', [
                'user_id' => $user->id,
                'type'    => $type->value,
                'error'   => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function isCategoryEnabled(User $user, NotificationCategory $category): bool
    {
        $pref = $user->notificationPreference;
        if ($pref === null) {
            return true;
        }

        return $pref->enabledFor($category);
    }
}
