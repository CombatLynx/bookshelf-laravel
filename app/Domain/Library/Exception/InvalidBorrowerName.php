<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class InvalidBorrowerName extends DomainException
{
    public static function because(string $raw): self
    {
        return new self('Укажите имя читателя — от 1 до 80 символов, получено: «' . $raw . '».');
    }
}
