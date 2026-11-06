<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class BookReservedForAnotherReader extends DomainException
{
    public static function named(string $title): self
    {
        return new self('«' . $title . '» отложена для другого читателя.');
    }
}
