<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftNotFoundException extends RuntimeException
{
    public function __construct(string $code = '')
    {
        parent::__construct("Gift with code {$code} not found");
    }
}
