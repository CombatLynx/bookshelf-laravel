<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookStatus;

/** Снимок книги для экрана. Представление не работает с агрегатом напрямую. */
final class BookView
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $author;

    /** @var string */
    public $isbn;

    /** @var string */
    public $status;

    private function __construct(string $id, string $title, string $author, string $isbn, string $status)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->status = $status;
    }

    public static function fromBook(Book $book): self
    {
        return new self(
            $book->id()->toString(),
            $book->title(),
            $book->author(),
            $book->isbn()->formatted(),
            $book->status()->value()
        );
    }

    public function isBorrowed(): bool
    {
        return $this->status === BookStatus::BORROWED;
    }

    public function statusLabel(): string
    {
        return $this->isBorrowed() ? 'На руках' : 'На полке';
    }
}
