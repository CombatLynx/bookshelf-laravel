<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class BookAlreadyReserved extends DomainException
{
    public static function named(string $title): self
    {
        return new self('«' . $title . '» уже забронирована другим читателем.');
    }
}
