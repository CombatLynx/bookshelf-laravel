<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Clock;
use App\Domain\Library\Exception\BookNotFound;
use App\Domain\Library\ReaderId;
use App\Domain\Library\ReservationPolicy;
use InvalidArgumentException;

final class ReturnBookHandler
{
    /** @var BookRepository */
    private $books;

    /** @var Clock */
    private $clock;

    /** @var ReservationPolicy */
    private $reservations;

    public function __construct(BookRepository $books, Clock $clock, ReservationPolicy $reservations)
    {
        $this->books = $books;
        $this->clock = $clock;
        $this->reservations = $reservations;
    }

    public function handle(string $bookId, string $readerId): bool
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

        $setAside = $book->giveBack(
            ReaderId::fromInt((int) $readerId),
            $this->clock->today(),
            $this->reservations
        );
        $this->books->save($book);

        return $setAside;
    }
}
