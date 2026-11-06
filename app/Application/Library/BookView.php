<?php

declare(strict_types=1);

namespace App\Application\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookStatus;
use App\Domain\Library\LendingPolicy;
use App\Domain\Library\ReaderId;
use App\Domain\Library\Reservation;
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

    /** @var bool */
    private $canManage;

    /** @var bool */
    private $canReserve;

    /** @var bool */
    private $canCancel;

    /** @var bool */
    private $canBorrow;

    /** @var bool */
    private $reserved;

    /** @var bool */
    private $reservedForMe;

    /** @var string|null */
    public $reservationNote;

    private function __construct(
        string $id,
        string $title,
        string $author,
        string $isbn,
        string $status,
        ?string $borrower,
        ?string $dueOn,
        bool $overdue,
        bool $canRenew,
        bool $canManage,
        bool $canReserve,
        bool $canCancel,
        bool $canBorrow,
        bool $reserved,
        bool $reservedForMe,
        ?string $reservationNote
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
        $this->canManage = $canManage;
        $this->canReserve = $canReserve;
        $this->canCancel = $canCancel;
        $this->canBorrow = $canBorrow;
        $this->reserved = $reserved;
        $this->reservedForMe = $reservedForMe;
        $this->reservationNote = $reservationNote;
    }

    public static function fromBook(Book $book, DateTimeImmutable $today, LendingPolicy $policy, ?ReaderId $reader): self
    {
        $loan = $book->loan();
        $reservation = $book->reservation();
        $held = $reader !== null && $book->isHeldBy($reader);
        $activeReservation = $reservation !== null && !$reservation->isExpired($today);
        $reservedForMe = $reader !== null && $activeReservation && $reservation->isHeldBy($reader);

        return new self(
            $book->id()->toString(),
            $book->title(),
            $book->author(),
            $book->isbn()->formatted(),
            $book->status()->value(),
            $loan === null ? null : $loan->borrower()->toString(),
            $loan === null ? null : $loan->dueOn()->format('d.m.Y'),
            $loan !== null && $loan->isOverdue($today),
            $held && $book->canBeRenewed($today, $policy),
            $held,
            $reader !== null && $book->canBeReservedBy($reader),
            $reader !== null && $book->canCancelReservation($reader) && $activeReservation,
            $reader !== null && $book->canBeBorrowedBy($reader, $today),
            $activeReservation,
            $reservedForMe,
            self::reservationNote($reservation, $today)
        );
    }

    private static function reservationNote(?Reservation $reservation, DateTimeImmutable $today): ?string
    {
        if ($reservation === null || $reservation->isExpired($today)) {
            return null;
        }

        if ($reservation->isReady() && $reservation->expiresOn() !== null) {
            return $reservation->name()->toString() . ', забрать до ' . $reservation->expiresOn()->format('d.m.Y');
        }

        return $reservation->name()->toString() . ' ждёт возврата';
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

    public function canManage(): bool
    {
        return $this->canManage;
    }

    public function canReserve(): bool
    {
        return $this->canReserve;
    }

    public function canCancelReservation(): bool
    {
        return $this->canCancel;
    }

    public function canBorrow(): bool
    {
        return $this->canBorrow;
    }

    public function isReserved(): bool
    {
        return $this->reserved;
    }

    public function statusLabel(): string
    {
        if ($this->isOverdue()) {
            return 'Просрочена';
        }

        if ($this->isBorrowed()) {
            return 'На руках';
        }

        if ($this->reserved) {
            return $this->reservedForMe ? 'Ждёт вас' : 'Забронирована';
        }

        return 'На полке';
    }
}
