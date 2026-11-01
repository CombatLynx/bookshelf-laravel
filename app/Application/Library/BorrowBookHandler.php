<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\BorrowerName;
use App\Domain\Library\Clock;
use App\Domain\Library\Exception\BookNotFound;
use App\Domain\Library\LendingPolicy;
use App\Domain\Library\ReaderId;
use InvalidArgumentException;

final class BorrowBookHandler
{
    /** @var BookRepository */
    private $books;

    /** @var LendingPolicy */
    private $policy;

    /** @var Clock */
    private $clock;

    public function __construct(BookRepository $books, LendingPolicy $policy, Clock $clock)
    {
        $this->books = $books;
        $this->policy = $policy;
        $this->clock = $clock;
    }

    public function handle(string $bookId, string $readerId, string $readerName): void
    {
        $book = $this->books->findById($this->parseId($bookId));

        if ($book === null) {
            throw BookNotFound::withId($bookId);
        }

        $book->borrow(
            BorrowerName::fromString($readerName),
            ReaderId::fromInt((int) $readerId),
            $this->clock->today(),
            $this->policy
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
