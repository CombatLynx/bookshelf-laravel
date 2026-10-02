<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\BookAlreadyBorrowed;
use App\Domain\Library\Exception\BookIsNotBorrowed;
use App\Domain\Library\Exception\InvalidBookData;
use DateTimeImmutable;

/**
 * Агрегат «Книга». Выдать можно только с полки и только читателю,
 * вернуть — только с рук. Срок и число продлений держит вложенная выдача.
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

    /** @var Loan|null */
    private $loan;

    private function __construct(
        BookId $id,
        string $title,
        string $author,
        Isbn $isbn,
        BookStatus $status,
        ?Loan $loan
    ) {
        $title = trim($title);
        $author = trim($author);

        if ($title === '' || $author === '') {
            throw InvalidBookData::emptyTitleOrAuthor();
        }

        if ($status->isBorrowed() !== ($loan !== null)) {
            throw InvalidBookData::inconsistentLoan();
        }

        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->status = $status;
        $this->loan = $loan;
    }

    public static function register(BookId $id, string $title, string $author, Isbn $isbn): self
    {
        return new self($id, $title, $author, $isbn, BookStatus::available(), null);
    }

    public static function reconstitute(
        BookId $id,
        string $title,
        string $author,
        Isbn $isbn,
        BookStatus $status,
        ?Loan $loan
    ): self {
        return new self($id, $title, $author, $isbn, $status, $loan);
    }

    public function borrow(BorrowerName $borrower, DateTimeImmutable $on, LendingPolicy $policy): void
    {
        if ($this->status->isBorrowed()) {
            throw BookAlreadyBorrowed::named($this->title);
        }

        $this->loan = Loan::open($borrower, $on, $policy);
        $this->status = BookStatus::borrowed();
    }

    public function renew(DateTimeImmutable $on, LendingPolicy $policy): void
    {
        if ($this->loan === null) {
            throw BookIsNotBorrowed::named($this->title);
        }

        $this->loan = $this->loan->renew($on, $policy, $this->title);
    }

    public function giveBack(): void
    {
        if ($this->status->isAvailable() || $this->loan === null) {
            throw BookIsNotBorrowed::named($this->title);
        }

        $this->loan = null;
        $this->status = BookStatus::available();
    }

    public function canBeRenewed(DateTimeImmutable $on, LendingPolicy $policy): bool
    {
        if ($this->loan === null) {
            return false;
        }

        return !$this->loan->isOverdue($on) && $policy->allowsAnotherRenewal($this->loan->renewals());
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

    public function loan(): ?Loan
    {
        return $this->loan;
    }
}
