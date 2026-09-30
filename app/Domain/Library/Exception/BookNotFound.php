<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class BookNotFound extends DomainException
{
    public static function withId(string $id): self
    {
        return new self('Книга «' . $id . '» не найдена.');
    }
}
