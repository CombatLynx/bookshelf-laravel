<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\BookAlreadyBorrowed;
use App\Domain\Library\Exception\BookAlreadyReserved;
use App\Domain\Library\Exception\BookIsNotBorrowed;
use App\Domain\Library\Exception\BookReservedForAnotherReader;
use App\Domain\Library\Exception\CannotReserveBook;
use App\Domain\Library\Exception\InvalidBookData;
use App\Domain\Library\Exception\LoanBelongsToAnotherReader;
use App\Domain\Library\Exception\ReservationNotYours;
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

    /** @var Reservation|null */
    private $reservation;

    private function __construct(
        BookId $id,
        string $title,
        string $author,
        Isbn $isbn,
        BookStatus $status,
        ?Loan $loan,
        ?Reservation $reservation
    ) {
        $title = trim($title);
        $author = trim($author);

        if ($title === '' || $author === '') {
            throw InvalidBookData::emptyTitleOrAuthor();
        }

        if ($status->isBorrowed() !== ($loan !== null)) {
            throw InvalidBookData::inconsistentLoan();
        }

        if ($status->isAvailable() && $reservation !== null) {
            throw InvalidBookData::inconsistentLoan();
        }

        if ($status->isReserved() && ($reservation === null || !$reservation->isReady())) {
            throw InvalidBookData::inconsistentLoan();
        }

        if ($status->isBorrowed() && $reservation !== null && !$reservation->isWaiting()) {
            throw InvalidBookData::inconsistentLoan();
        }

        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->status = $status;
        $this->loan = $loan;
        $this->reservation = $reservation;
    }

    public static function register(BookId $id, string $title, string $author, Isbn $isbn): self
    {
        return new self($id, $title, $author, $isbn, BookStatus::available(), null, null);
    }

    public static function reconstitute(
        BookId $id,
        string $title,
        string $author,
        Isbn $isbn,
        BookStatus $status,
        ?Loan $loan,
        ?Reservation $reservation = null
    ): self {
        return new self($id, $title, $author, $isbn, $status, $loan, $reservation);
    }

    public function borrow(
        BorrowerName $borrower,
        ReaderId $reader,
        DateTimeImmutable $on,
        LendingPolicy $policy
    ): void {
        if ($this->status->isBorrowed()) {
            throw BookAlreadyBorrowed::named($this->title);
        }

        $this->dropExpiredReservation($on);

        if ($this->reservation !== null && !$this->reservation->isHeldBy($reader)) {
            throw BookReservedForAnotherReader::named($this->title);
        }

        $this->reservation = null;
        $this->loan = Loan::open($borrower, $reader, $on, $policy);
        $this->status = BookStatus::borrowed();
    }

    public function reserve(BorrowerName $name, ReaderId $reader, DateTimeImmutable $on): void
    {
        if (!$this->status->isBorrowed() || $this->loan === null) {
            throw CannotReserveBook::onTheShelf($this->title);
        }

        if ($this->loan->isHeldBy($reader)) {
            throw CannotReserveBook::heldByYou($this->title);
        }

        if ($this->reservation !== null) {
            throw BookAlreadyReserved::named($this->title);
        }

        $this->reservation = Reservation::waiting($reader, $name, $on);
    }

    public function cancelReservation(ReaderId $reader): void
    {
        if ($this->reservation === null || !$this->reservation->isHeldBy($reader)) {
            throw ReservationNotYours::named($this->title);
        }

        $this->reservation = null;

        if ($this->status->isReserved()) {
            $this->status = BookStatus::available();
        }
    }

    public function renew(ReaderId $reader, DateTimeImmutable $on, LendingPolicy $policy): void
    {
        $this->assertHeldBy($reader);
        $this->loan = $this->loan->renew($on, $policy, $this->title);
    }

    public function giveBack(ReaderId $reader, DateTimeImmutable $on, ReservationPolicy $policy): bool
    {
        $this->assertHeldBy($reader);
        $this->loan = null;

        if ($this->reservation === null) {
            $this->status = BookStatus::available();

            return false;
        }

        $this->reservation = $this->reservation->markReady($on, $policy);
        $this->status = BookStatus::reserved();

        return true;
    }

    public function isHeldBy(ReaderId $reader): bool
    {
        return $this->loan !== null && $this->loan->isHeldBy($reader);
    }

    public function canBeReservedBy(ReaderId $reader): bool
    {
        return $this->status->isBorrowed()
            && $this->loan !== null
            && !$this->loan->isHeldBy($reader)
            && $this->reservation === null;
    }

    public function canCancelReservation(ReaderId $reader): bool
    {
        return $this->reservation !== null && $this->reservation->isHeldBy($reader);
    }

    public function canBeBorrowedBy(ReaderId $reader, DateTimeImmutable $on): bool
    {
        if ($this->status->isBorrowed()) {
            return false;
        }

        if ($this->reservation === null || $this->reservation->isExpired($on)) {
            return true;
        }

        return $this->reservation->isHeldBy($reader);
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

    public function reservation(): ?Reservation
    {
        return $this->reservation;
    }

    private function dropExpiredReservation(DateTimeImmutable $on): void
    {
        if ($this->reservation === null || !$this->reservation->isExpired($on)) {
            return;
        }

        $this->reservation = null;

        if ($this->status->isReserved()) {
            $this->status = BookStatus::available();
        }
    }

    private function assertHeldBy(ReaderId $reader): void
    {
        if ($this->status->isAvailable() || $this->loan === null) {
            throw BookIsNotBorrowed::named($this->title);
        }

        if (!$this->loan->isHeldBy($reader)) {
            throw LoanBelongsToAnotherReader::named($this->title);
        }
    }
}
