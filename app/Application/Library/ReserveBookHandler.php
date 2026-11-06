<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\BorrowerName;
use App\Domain\Library\Clock;
use App\Domain\Library\Exception\BookNotFound;
use App\Domain\Library\ReaderId;
use InvalidArgumentException;

final class ReserveBookHandler
{
    /** @var BookRepository */
    private $books;

    /** @var Clock */
    private $clock;

    public function __construct(BookRepository $books, Clock $clock)
    {
        $this->books = $books;
        $this->clock = $clock;
    }

    public function handle(string $bookId, string $readerId, string $readerName): void
    {
        $book = $this->books->findById($this->parseId($bookId));

        if ($book === null) {
            throw BookNotFound::withId($bookId);
        }

        $book->reserve(
            BorrowerName::fromString($readerName),
            ReaderId::fromInt((int) $readerId),
            $this->clock->today()
        );
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
