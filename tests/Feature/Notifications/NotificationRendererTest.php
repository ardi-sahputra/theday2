<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Services\Notifications\NotificationRenderer;
use Tests\TestCase;

class NotificationRendererTest extends TestCase
{
    public function test_renders_translation_key_with_placeholders_and_count(): void
    {
        $renderer = app(NotificationRenderer::class);

        $title = $renderer->render(
            NotificationType::GuestMessageCreated,
            ['invitation_title' => 'Andi & Sari'],
            count: 5,
            locale: 'id',
        );

        $this->assertSame('5 ucapan baru di Andi & Sari', $title);
    }

    public function test_falls_back_to_app_locale_when_locale_null(): void
    {
        $this->app->setLocale('id');
        $renderer = app(NotificationRenderer::class);

        $title = $renderer->render(
            NotificationType::GuestMessageCreated,
            ['invitation_title' => 'X & Y'],
            count: 1,
            locale: null,
        );

        $this->assertStringContainsString('ucapan baru', $title);
    }
}
