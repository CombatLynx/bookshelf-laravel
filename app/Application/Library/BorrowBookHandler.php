<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Exception\BookNotFound;
use InvalidArgumentException;

final class BorrowBookHandler
{
    /** @var BookRepository */
    private $books;

    public function __construct(BookRepository $books)
    {
        $this->books = $books;
    }

    public function handle(string $bookId): void
    {
        $book = $this->books->findById($this->parseId($bookId));

        if ($book === null) {
            throw BookNotFound::withId($bookId);
        }

        $book->borrow();
        $this->books->save($book);
    }

    private function parseId(string $bookId): BookId
    {
        try {
            return BookId::fromString($bookId);
        } catch (InvalidArgumentException $exception) {
            throw BookNotFound::withId($bookId);
        }
    }
}
