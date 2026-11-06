<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookId;
use App\Domain\Library\BookStatus;
use App\Domain\Library\BorrowerName;
use App\Domain\Library\Exception\BookAlreadyReserved;
use App\Domain\Library\Exception\BookReservedForAnotherReader;
use App\Domain\Library\Exception\CannotReserveBook;
use App\Domain\Library\Isbn;
use App\Domain\Library\LendingPolicy;
use App\Domain\Library\ReaderId;
use App\Domain\Library\ReservationPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReservationTest extends TestCase
{
    public function test_next_reader_can_reserve_a_borrowed_book_and_pick_it_up(): void
    {
        $book = $this->borrowedBook();
        $book->reserve($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-05'));

        $setAside = $book->giveBack($this->reader(), new DateTimeImmutable('2026-10-10'), new ReservationPolicy());

        $this->assertTrue($setAside);
        $this->assertTrue($book->status()->isReserved());
        $this->assertSame('2026-10-13', $book->reservation()->expiresOn()->format('Y-m-d'));

        $book->borrow($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-11'), new LendingPolicy());

        $this->assertTrue($book->status()->isBorrowed());
        $this->assertNull($book->reservation());
        $this->assertSame('2', $book->loan()->readerId()->toString());
    }

    public function test_holder_cannot_reserve_their_own_book(): void
    {
        $this->expectException(CannotReserveBook::class);

        $this->borrowedBook()->reserve($this->name('Анна'), $this->reader(), new DateTimeImmutable('2026-10-05'));
    }

    public function test_shelf_book_cannot_be_reserved(): void
    {
        $this->expectException(CannotReserveBook::class);

        $this->book()->reserve($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-05'));
    }

    public function test_second_reservation_is_refused(): void
    {
        $book = $this->borrowedBook();
        $book->reserve($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-05'));

        $this->expectException(BookAlreadyReserved::class);

        $book->reserve($this->name('Вера'), ReaderId::fromInt(3), new DateTimeImmutable('2026-10-06'));
    }

    public function test_someone_else_cannot_take_a_book_held_for_pickup(): void
    {
        $book = $this->borrowedBook();
        $book->reserve($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-05'));
        $book->giveBack($this->reader(), new DateTimeImmutable('2026-10-10'), new ReservationPolicy());

        $this->expectException(BookReservedForAnotherReader::class);

        $book->borrow($this->name('Анна'), $this->reader(), new DateTimeImmutable('2026-10-11'), new LendingPolicy());
    }

    public function test_expired_hold_returns_the_book_to_the_shelf(): void
    {
        $book = $this->borrowedBook();
        $book->reserve($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-05'));
        $book->giveBack($this->reader(), new DateTimeImmutable('2026-10-10'), new ReservationPolicy());

        $book->borrow($this->name('Вера'), ReaderId::fromInt(3), new DateTimeImmutable('2026-10-14'), new LendingPolicy());

        $this->assertSame(BookStatus::BORROWED, $book->status()->value());
        $this->assertSame('3', $book->loan()->readerId()->toString());
        $this->assertNull($book->reservation());
    }

    public function test_reader_can_cancel_a_reservation_before_pickup(): void
    {
        $book = $this->borrowedBook();
        $book->reserve($this->name('Борис'), ReaderId::fromInt(2), new DateTimeImmutable('2026-10-05'));
        $book->giveBack($this->reader(), new DateTimeImmutable('2026-10-10'), new ReservationPolicy());

        $book->cancelReservation(ReaderId::fromInt(2));

        $this->assertTrue($book->status()->isAvailable());
        $this->assertNull($book->reservation());
    }

    private function book(): Book
    {
        return Book::register(
            BookId::fromString('11111111-1111-4111-8111-111111111111'),
            'Domain-Driven Design',
            'Eric Evans',
            Isbn::fromString('978-0-321-12521-7')
        );
    }

    private function borrowedBook(): Book
    {
        $book = $this->book();
        $book->borrow($this->name('Анна'), $this->reader(), new DateTimeImmutable('2026-10-01'), new LendingPolicy());

        return $book;
    }

    private function reader(): ReaderId
    {
        return ReaderId::fromInt(1);
    }

    private function name(string $name): BorrowerName
    {
        return BorrowerName::fromString($name);
    }
}
