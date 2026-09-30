<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\BookAlreadyBorrowed;
use App\Domain\Library\Exception\BookIsNotBorrowed;
use App\Domain\Library\Exception\InvalidBookData;

/**
 * Агрегат «Книга». Следит за инвариантом: книгу можно выдать только с полки
 * и вернуть только если она уже на руках.
 */
final class Book
{
    /** @var BookId */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $author;

    /** @var Isbn */
    private $isbn;

    /** @var BookStatus */
    private $status;

    private function __construct(BookId $id, string $title, string $author, Isbn $isbn, BookStatus $status)
    {
        $title = trim($title);
        $author = trim($author);

        if ($title === '' || $author === '') {
            throw InvalidBookData::emptyTitleOrAuthor();
        }

        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->status = $status;
    }

    public static function register(BookId $id, string $title, string $author, Isbn $isbn): self
    {
        return new self($id, $title, $author, $isbn, BookStatus::available());
    }

    public static function reconstitute(BookId $id, string $title, string $author, Isbn $isbn, BookStatus $status): self
    {
        return new self($id, $title, $author, $isbn, $status);
    }

    public function borrow(): void
    {
        if ($this->status->isBorrowed()) {
            throw BookAlreadyBorrowed::named($this->title);
        }

        $this->status = BookStatus::borrowed();
    }

    public function giveBack(): void
    {
        if ($this->status->isAvailable()) {
            throw BookIsNotBorrowed::named($this->title);
        }

        $this->status = BookStatus::available();
    }

    public function id(): BookId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function status(): BookStatus
    {
        return $this->status;
    }
}
