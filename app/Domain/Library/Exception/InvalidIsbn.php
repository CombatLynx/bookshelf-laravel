<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class InvalidIsbn extends DomainException
{
    public static function because(string $raw): self
    {
        return new self(
            'ISBN «' . $raw . '» не проходит проверку: нужны 13 цифр и верная контрольная сумма.'
        );
    }
}
