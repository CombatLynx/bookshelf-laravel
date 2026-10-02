<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Clock;
use App\Domain\Library\LendingPolicy;

final class ListBooksHandler
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

    /**
     * @return BookView[]
     */
    public function handle(): array
    {
        $today = $this->clock->today();
        $policy = $this->policy;

        return array_map(static function (Book $book) use ($today, $policy) {
            return BookView::fromBook($book, $today, $policy);
        }, $this->books->all());
    }
}
