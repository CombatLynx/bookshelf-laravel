<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class LoanCannotBeRenewed extends DomainException
{
    public static function overdue(string $title): self
    {
        return new self('«' . $title . '» просрочена, продлить выдачу нельзя.');
    }

    public static function limitReached(string $title): self
    {
        return new self('«' . $title . '» можно продлить только один раз.');
    }
}
