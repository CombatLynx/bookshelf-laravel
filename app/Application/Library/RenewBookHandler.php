<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Clock;
use App\Domain\Library\Exception\BookNotFound;
use App\Domain\Library\LendingPolicy;
use InvalidArgumentException;

final class RenewBookHandler
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

        $book->renew($this->clock->today(), $this->policy);
        $this->books->save($book);
    }
}
