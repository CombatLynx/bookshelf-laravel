<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Application\Library\Exception\DuplicateIsbn;
use App\Domain\Library\Book;
use App\Domain\Library\BookRepository;
use App\Domain\Library\Isbn;

final class RegisterBookHandler
{
    /** @var BookRepository */
    private $books;

    public function __construct(BookRepository $books)
    {
        $this->books = $books;
    }

    public function handle(string $title, string $author, string $isbn): string
    {
        $isbnValue = Isbn::fromString($isbn);

        if ($this->books->findByIsbn($isbnValue) !== null) {
            throw DuplicateIsbn::forIsbn($isbnValue->formatted());
        }

        $book = Book::register($this->books->nextIdentity(), $title, $author, $isbnValue);
        $this->books->save($book);

        return $book->id()->toString();
    }
}
