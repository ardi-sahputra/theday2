<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InternalOrSameHostUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        if (str_starts_with($value, '/')) {
            if (str_starts_with($value, '//')) {
                $fail('The :attribute is invalid.');

                return;
            }

            return;
        }

        $parts = parse_url($value);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        if (! in_array($parts['scheme'], ['http', 'https'], true)) {
            $fail('The :attribute scheme is not allowed.');

            return;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if ($parts['host'] !== $appHost) {
            $fail('The :attribute must be on the same host.');
        }
    }
}
