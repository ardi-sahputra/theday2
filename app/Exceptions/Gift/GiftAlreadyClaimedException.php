<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftAlreadyClaimedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Gift has already been claimed');
    }
}
