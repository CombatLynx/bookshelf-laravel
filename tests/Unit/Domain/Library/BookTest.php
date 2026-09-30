<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Library;

use App\Domain\Library\Book;
use App\Domain\Library\BookId;
use App\Domain\Library\Exception\BookAlreadyBorrowed;
use App\Domain\Library\Exception\BookIsNotBorrowed;
use App\Domain\Library\Exception\InvalidBookData;
use App\Domain\Library\Isbn;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    public function test_new_book_is_on_the_shelf(): void
    {
        $book = $this->book();

        $this->assertTrue($book->status()->isAvailable());
        $this->assertSame('Domain-Driven Design', $book->title());
    }

    public function test_book_can_be_borrowed_and_returned(): void
    {
        $book = $this->book();

        $book->borrow();
        $this->assertTrue($book->status()->isBorrowed());

        $book->giveBack();
        $this->assertTrue($book->status()->isAvailable());
    }

    public function test_borrowed_book_cannot_be_borrowed_again(): void
    {
        $book = $this->book();
        $book->borrow();

        $this->expectException(BookAlreadyBorrowed::class);

        $book->borrow();
    }

    public function test_available_book_cannot_be_returned(): void
    {
        $this->expectException(BookIsNotBorrowed::class);

        $this->book()->giveBack();
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

    private function book(): Book
    {
        return Book::register(
            BookId::fromString('11111111-1111-4111-8111-111111111111'),
            'Domain-Driven Design',
            'Eric Evans',
            Isbn::fromString('978-0-321-12521-7')
        );
    }
}
