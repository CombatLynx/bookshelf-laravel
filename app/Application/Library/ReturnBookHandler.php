<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Exception\BookNotFound;
use InvalidArgumentException;

final class ReturnBookHandler
{
    /** @var BookRepository */
    private $books;

    public function __construct(BookRepository $books)
    {
        $this->books = $books;
    }

    public function handle(string $bookId): void
    {
        try {
            $id = BookId::fromString($bookId);
        } catch (InvalidArgumentException $exception) {
            throw BookNotFound::withId($bookId);
        }

        $book = $this->books->findById($id);

        if ($book === null) {
            throw BookNotFound::withId($bookId);
        }

        $book->giveBack();
        $this->books->save($book);
    }
}
