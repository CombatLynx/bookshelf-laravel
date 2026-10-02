<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookStatus;
use App\Domain\Library\LendingPolicy;
use DateTimeImmutable;

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

    /** @var string|null */
    public $borrower;

    /** @var string|null */
    public $dueOn;

    /** @var bool */
    private $overdue;

    /** @var bool */
    private $canRenew;

    private function __construct(
        string $id,
        string $title,
        string $author,
        string $isbn,
        string $status,
        ?string $borrower,
        ?string $dueOn,
        bool $overdue,
        bool $canRenew
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->status = $status;
        $this->borrower = $borrower;
        $this->dueOn = $dueOn;
        $this->overdue = $overdue;
        $this->canRenew = $canRenew;
    }

    public static function fromBook(Book $book, DateTimeImmutable $today, LendingPolicy $policy): self
    {
        $loan = $book->loan();

        return new self(
            $book->id()->toString(),
            $book->title(),
            $book->author(),
            $book->isbn()->formatted(),
            $book->status()->value(),
            $loan === null ? null : $loan->borrower()->toString(),
            $loan === null ? null : $loan->dueOn()->format('d.m.Y'),
            $loan !== null && $loan->isOverdue($today),
            $book->canBeRenewed($today, $policy)
        );
    }

    public function isBorrowed(): bool
    {
        return $this->status === BookStatus::BORROWED;
    }

    public function isOverdue(): bool
    {
        return $this->overdue;
    }

    public function canBeRenewed(): bool
    {
        return $this->canRenew;
    }

    public function statusLabel(): string
    {
        if ($this->isOverdue()) {
            return 'Просрочена';
        }

        return $this->isBorrowed() ? 'На руках' : 'На полке';
    }
}
