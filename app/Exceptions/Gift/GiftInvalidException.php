<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftInvalidException extends RuntimeException
{
    public function __construct(string $reason = 'Gift is invalid')
    {
        parent::__construct($reason);
    }
}
