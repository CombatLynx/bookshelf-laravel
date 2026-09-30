<?php

declare(strict_types=1);

namespace App\Domain\Library;

use InvalidArgumentException;

final class BookId
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value)) {
            throw new InvalidArgumentException('Идентификатор книги должен быть UUID.');
        }

        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
