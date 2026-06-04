<?php

// app/Enums/WeddingDocumentStatus.php

declare(strict_types=1);

namespace App\Enums;

enum WeddingDocumentStatus: string
{
    case Belum  = 'belum';
    case Proses = 'proses';
    case Beres  = 'beres';
}
