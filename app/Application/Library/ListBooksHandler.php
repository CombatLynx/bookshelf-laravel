<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookRepository;

final class ListBooksHandler
{
    /** @var BookRepository */
    private $books;

    public function __construct(BookRepository $books)
    {
        $this->books = $books;
    }

    /**
     * @return BookView[]
     */
    public function handle(): array
    {
        return array_map(static function (Book $book) {
            return BookView::fromBook($book);
        }, $this->books->all());
    }
}
