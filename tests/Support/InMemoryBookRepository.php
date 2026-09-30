<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Library\Book;
use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Isbn;

final class InMemoryBookRepository implements BookRepository
{
    /** @var array<string, Book> */
    private $items = [];

    /** @var int */
    private $sequence = 0;

    public function nextIdentity(): BookId
    {
        $this->sequence++;

        return BookId::fromString(sprintf('00000000-0000-4000-8000-%012d', $this->sequence));
    }

    public function save(Book $book): void
    {
        $this->items[$book->id()->toString()] = $book;
    }

    public function findById(BookId $id): ?Book
    {
        return $this->items[$id->toString()] ?? null;
    }

    public function findByIsbn(Isbn $isbn): ?Book
    {
        foreach ($this->items as $book) {
            if ($book->isbn()->equals($isbn)) {
                return $book;
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_values($this->items);
    }
}
