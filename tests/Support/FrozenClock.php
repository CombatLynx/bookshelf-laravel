<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Library\Clock;
use DateTimeImmutable;

final class FrozenClock implements Clock
{
    /** @var DateTimeImmutable */
    private $today;

    public function __construct(DateTimeImmutable $today)
    {
        $this->today = $today;
    }

    public function today(): DateTimeImmutable
    {
        return $this->today;
    }
}
