<?php

declare(strict_types=1);

namespace App\Domain\Library\Exception;

use DomainException;

final class CannotReserveBook extends DomainException
{
    public static function onTheShelf(string $title): self
    {
        return new self('«' . $title . '» на полке, её можно взять сразу, без брони.');
    }

    public static function heldByYou(string $title): self
    {
        return new self('«' . $title . '» уже у вас, бронировать её не нужно.');
    }
}
