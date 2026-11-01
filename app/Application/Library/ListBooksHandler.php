<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Clock;
use App\Domain\Library\LendingPolicy;
use App\Domain\Library\ReaderId;

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
    public function handle(?ReaderId $reader = null): array
    {
        $today = $this->clock->today();
        $policy = $this->policy;

        return array_map(static function (Book $book) use ($today, $policy, $reader) {
            return BookView::fromBook($book, $today, $policy, $reader);
        }, $this->books->all());
    }
}
