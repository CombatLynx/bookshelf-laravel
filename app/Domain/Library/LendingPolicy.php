<?php

declare(strict_types=1);

namespace App\Domain\Library;

use DateTimeImmutable;
use RuntimeException;

/**
 * Политика выдачи: срок 14 дней, продлить можно один раз
 * и только пока книга не просрочена. Новый срок считается от дня продления.
 */
final class LendingPolicy
{
    public const TERM_IN_DAYS = 14;

    public const MAX_RENEWALS = 1;

    public function dueOn(DateTimeImmutable $from): DateTimeImmutable
    {
        $due = $from->setTime(0, 0)->modify('+' . self::TERM_IN_DAYS . ' days');

        if (!$due instanceof DateTimeImmutable) {
            throw new RuntimeException('Не удалось посчитать дату возврата.');
        }

        return $due;
    }

    public function allowsAnotherRenewal(int $renewals): bool
    {
        return $renewals < self::MAX_RENEWALS;
    }
}
