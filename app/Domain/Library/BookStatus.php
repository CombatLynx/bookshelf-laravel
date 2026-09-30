<?php

declare(strict_types=1);

namespace App\Domain\Library;

use InvalidArgumentException;

final class BookStatus
{
    public const AVAILABLE = 'available';
    public const BORROWED = 'borrowed';

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        if (!in_array($value, [self::AVAILABLE, self::BORROWED], true)) {
            throw new InvalidArgumentException('Неизвестный статус книги.');
        }

        $this->value = $value;
    }

    public static function available(): self
    {
        return new self(self::AVAILABLE);
    }

    public static function borrowed(): self
    {
        return new self(self::BORROWED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isBorrowed(): bool
    {
        return $this->value === self::BORROWED;
    }

    public function isAvailable(): bool
    {
        return $this->value === self::AVAILABLE;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
