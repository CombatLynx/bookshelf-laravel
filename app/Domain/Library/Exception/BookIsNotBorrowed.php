<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class BookIsNotBorrowed extends DomainException
{
    public static function named(string $title): self
    {
        return new self('«' . $title . '» и так на полке, возвращать её не нужно.');
    }
}
