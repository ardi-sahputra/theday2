<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Rules\InternalOrSameHostUrl;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class InternalOrSameHostUrlTest extends TestCase
{
    /** @dataProvider validUrls */
    public function test_valid(string $url): void
    {
        config(['app.url' => 'https://theday.app']);
        $v = Validator::make(['u' => $url], ['u' => [new InternalOrSameHostUrl()]]);
        $this->assertTrue($v->passes(), "Expected pass for: {$url}");
    }

    public static function validUrls(): array
    {
        return [
            ['/dashboard'],
            ['/dashboard/invitations/123?x=1'],
            ['https://theday.app/dashboard'],
        ];
    }

    /** @dataProvider invalidUrls */
    public function test_invalid(string $url): void
    {
        config(['app.url' => 'https://theday.app']);
        $v = Validator::make(['u' => $url], ['u' => [new InternalOrSameHostUrl()]]);
        $this->assertFalse($v->passes(), "Expected fail for: {$url}");
    }

    public static function invalidUrls(): array
    {
        return [
            ['javascript:alert(1)'],
            ['data:text/html,evil'],
            ['https://attacker.com/x'],
            ['not-a-url'],
        ];
    }
}
