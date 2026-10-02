<?php

declare(strict_types=1);

namespace App\Domain\Library;

use DateTimeImmutable;

/** Порт «который сегодня день». Домен не вызывает системные часы сам. */
interface Clock
{
    public function today(): DateTimeImmutable;
}
