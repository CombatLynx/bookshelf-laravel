<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class InvalidBookData extends DomainException
{
    public static function emptyTitleOrAuthor(): self
    {
        return new self('Название и автор не могут быть пустыми.');
    }
}
