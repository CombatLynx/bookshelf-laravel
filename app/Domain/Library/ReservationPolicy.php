<?php

declare(strict_types=1);

namespace App\Domain\Library;

use DateTimeImmutable;
use RuntimeException;

/** Сколько дней забронированная книга ждёт того, кто её заказал. */
final class ReservationPolicy
{
    public const PICKUP_DAYS = 3;

    public function expiresOn(DateTimeImmutable $from): DateTimeImmutable
    {
        $expires = $from->setTime(0, 0)->modify('+' . self::PICKUP_DAYS . ' days');

        if (!$expires instanceof DateTimeImmutable) {
            throw new RuntimeException('Не удалось посчитать срок брони.');
        }

        return $expires;
    }
}
