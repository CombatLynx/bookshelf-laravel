<?php

declare(strict_types=1);

namespace App\Application\Library\Exception;

use DomainException;

final class DuplicateIsbn extends DomainException
{
    public static function forIsbn(string $isbn): self
    {
        return new self('Книга с ISBN ' . $isbn . ' уже стоит на полке.');
    }
}
