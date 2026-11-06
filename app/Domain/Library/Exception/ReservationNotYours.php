<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class ReservationNotYours extends DomainException
{
    public static function named(string $title): self
    {
        return new self('Снять бронь «' . $title . '» может только тот, кто её оформил.');
    }
}
