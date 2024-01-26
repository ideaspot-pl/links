<?php

declare(strict_types=1);

namespace App\Enum;

enum LinkStatusEnum: int
{
    case STATUS_ACTIVE = 1;
    case STATUS_INACTIVE = 0;
}
