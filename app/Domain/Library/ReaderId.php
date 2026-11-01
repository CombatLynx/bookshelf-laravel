<?php

declare(strict_types=1);

namespace App\Domain\Library;

use InvalidArgumentException;

/** Идентификатор читателя. В домен не попадает модель пользователя. */
final class ReaderId
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromInt(int $id): self
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Идентификатор читателя должен быть положительным.');
        }

        return new self((string) $id);
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
