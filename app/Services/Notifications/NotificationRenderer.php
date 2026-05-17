<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationType;

class NotificationRenderer
{
    /**
     * Render a notification title using its translation key.
     *
     * @param array<string,scalar> $payload Placeholder values (e.g. invitation_title, guest_name).
     */
    public function render(
        NotificationType $type,
        array $payload,
        int $count = 1,
        ?string $locale = null,
    ): string {
        $locale = $locale ?: app()->getLocale();

        // Admin broadcasts carry an ad-hoc title from the broadcast row; bypass translation.
        if ($type === NotificationType::SystemBroadcast) {
            return (string) ($payload['title_raw'] ?? '');
        }

        $rendered = trans(
            $type->translationKey(),
            array_merge($payload, ['count' => $count]),
            $locale,
        );

        // Translation strings can use either `:count` (Laravel-native, handled by trans())
        // or `{count}` (ICU-style, also used by the Vue side). Replace `{count}` here so
        // PHP-rendered titles work regardless of which style the template uses.
        $replacements = array_merge($payload, ['count' => $count]);
        foreach ($replacements as $key => $value) {
            $rendered = str_replace('{' . $key . '}', (string) $value, $rendered);
        }

        return $rendered;
    }
}
