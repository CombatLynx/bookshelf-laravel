<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\InvalidBorrowerName;

/** Имя читателя, которому выдана книга. */
final class BorrowerName
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $raw): self
    {
        $name = trim($raw);
        $length = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);

        if ($name === '' || $length > 80) {
            throw InvalidBorrowerName::because($raw);
        }

        return new self($name);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
