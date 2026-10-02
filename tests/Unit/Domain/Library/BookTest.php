<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookId;
use App\Domain\Library\BookStatus;
use App\Domain\Library\BorrowerName;
use App\Domain\Library\Exception\BookAlreadyBorrowed;
use App\Domain\Library\Exception\BookIsNotBorrowed;
use App\Domain\Library\Exception\InvalidBookData;
use App\Domain\Library\Exception\InvalidBorrowerName;
use App\Domain\Library\Exception\LoanCannotBeRenewed;
use App\Domain\Library\Isbn;
use App\Domain\Library\LendingPolicy;
use App\Domain\Library\Loan;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    public function test_new_book_is_on_the_shelf(): void
    {
        $book = $this->book();

        $this->assertTrue($book->status()->isAvailable());
        $this->assertNull($book->loan());
        $this->assertSame('Domain-Driven Design', $book->title());
    }

    public function test_book_can_be_borrowed_and_returned(): void
    {
        $book = $this->book();
        $on = new DateTimeImmutable('2026-10-02');

        $book->borrow($this->anna(), $on, $this->policy());

        $this->assertTrue($book->status()->isBorrowed());
        $this->assertSame('Анна', $book->loan()->borrower()->toString());
        $this->assertSame('2026-10-16', $book->loan()->dueOn()->format('Y-m-d'));
        $this->assertTrue($book->canBeRenewed($on, $this->policy()));

        $book->giveBack();

        $this->assertTrue($book->status()->isAvailable());
        $this->assertNull($book->loan());
    }

    public function test_loan_can_be_renewed_once(): void
    {
        $book = $this->book();
        $policy = $this->policy();
        $book->borrow($this->anna(), new DateTimeImmutable('2026-10-01'), $policy);

        $book->renew(new DateTimeImmutable('2026-10-10'), $policy);

        $this->assertSame(1, $book->loan()->renewals());
        $this->assertSame('2026-10-24', $book->loan()->dueOn()->format('Y-m-d'));
        $this->assertFalse($book->canBeRenewed(new DateTimeImmutable('2026-10-10'), $policy));
    }

    public function test_second_renewal_is_refused(): void
    {
        $book = $this->borrowedBook();
        $policy = $this->policy();
        $book->renew(new DateTimeImmutable('2026-10-10'), $policy);

        $this->expectException(LoanCannotBeRenewed::class);

        $book->renew(new DateTimeImmutable('2026-10-12'), $policy);
    }

    public function test_overdue_loan_cannot_be_renewed(): void
    {
        $book = $this->borrowedBook();

        $this->expectException(LoanCannotBeRenewed::class);

        $book->renew(new DateTimeImmutable('2026-10-20'), $this->policy());
    }

    public function test_borrowed_book_cannot_be_borrowed_again(): void
    {
        $book = $this->borrowedBook();

        $this->expectException(BookAlreadyBorrowed::class);

        $book->borrow($this->anna(), new DateTimeImmutable('2026-10-03'), $this->policy());
    }

    public function test_available_book_cannot_be_returned(): void
    {
        $this->expectException(BookIsNotBorrowed::class);

        $this->book()->giveBack();
    }

    public function test_shelf_book_cannot_be_renewed(): void
    {
        $this->expectException(BookIsNotBorrowed::class);

        $this->book()->renew(new DateTimeImmutable('2026-10-02'), $this->policy());
    }

    public function test_title_is_required(): void
    {
        $this->expectException(InvalidBookData::class);

        Book::register(
            BookId::fromString('11111111-1111-4111-8111-111111111111'),
            '   ',
            'Eric Evans',
            Isbn::fromString('9780321125217')
        );
    }

    public function test_borrower_name_is_required(): void
    {
        $this->expectException(InvalidBorrowerName::class);

        BorrowerName::fromString('   ');
    }

    public function test_borrowed_book_must_carry_a_loan(): void
    {
        $this->expectException(InvalidBookData::class);

        Book::reconstitute(
            BookId::fromString('11111111-1111-4111-8111-111111111111'),
            'Domain-Driven Design',
            'Eric Evans',
            Isbn::fromString('9780321125217'),
            BookStatus::borrowed(),
            null
        );
    }

    public function test_shelf_book_cannot_carry_a_loan(): void
    {
        $this->expectException(InvalidBookData::class);

        Book::reconstitute(
            BookId::fromString('11111111-1111-4111-8111-111111111111'),
            'Domain-Driven Design',
            'Eric Evans',
            Isbn::fromString('9780321125217'),
            BookStatus::available(),
            Loan::open($this->anna(), new DateTimeImmutable('2026-10-02'), $this->policy())
        );
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
        $book->borrow($this->anna(), new DateTimeImmutable('2026-10-01'), $this->policy());

        return $book;
    }

    private function anna(): BorrowerName
    {
        return BorrowerName::fromString('Анна');
    }

    private function policy(): LendingPolicy
    {
        return new LendingPolicy();
    }
}
