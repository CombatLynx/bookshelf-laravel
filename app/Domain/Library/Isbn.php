<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\InvalidIsbn;

final class Isbn
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $raw): self
    {
        $compact = str_replace(['-', ' '], '', trim($raw));

        if (!preg_match('/^\d{13}$/', $compact) || !self::checksumIsValid($compact)) {
            throw InvalidIsbn::because($raw);
        }

        return new self($compact);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function formatted(): string
    {
        return substr($this->value, 0, 3)
            . '-' . substr($this->value, 3, 1)
            . '-' . substr($this->value, 4, 3)
            . '-' . substr($this->value, 7, 5)
            . '-' . substr($this->value, 12, 1);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function checksumIsValid(string $digits): bool
    {
        $sum = 0;

        for ($index = 0; $index < 13; $index++) {
            $weight = $index % 2 === 0 ? 1 : 3;
            $sum += (int) $digits[$index] * $weight;
        }

        return $sum % 10 === 0;
    }
}
