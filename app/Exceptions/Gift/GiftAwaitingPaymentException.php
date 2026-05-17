<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftAwaitingPaymentException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Gift is awaiting payment confirmation');
    }
}
