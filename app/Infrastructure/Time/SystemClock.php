<?php

declare(strict_types=1);

namespace App\Infrastructure\Time;

use App\Domain\Library\Clock;
use DateTimeImmutable;

final class SystemClock implements Clock
{
    public function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }
}
