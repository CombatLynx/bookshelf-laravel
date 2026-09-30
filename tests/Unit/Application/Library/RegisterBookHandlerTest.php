<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Library;

use App\Application\Library\BorrowBookHandler;
use App\Application\Library\Exception\DuplicateIsbn;
use App\Application\Library\RegisterBookHandler;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryBookRepository;

final class RegisterBookHandlerTest extends TestCase
{
    public function test_same_isbn_cannot_be_registered_twice(): void
    {
        $books = new InMemoryBookRepository();
        $register = new RegisterBookHandler($books);

        $register->handle('Domain-Driven Design', 'Eric Evans', '9780321125217');

        $this->expectException(DuplicateIsbn::class);

        $register->handle('Другая обложка', 'Кто-то', '978-0-321-12521-7');
    }

    public function test_borrow_goes_through_the_aggregate(): void
    {
        $books = new InMemoryBookRepository();
        $id = (new RegisterBookHandler($books))->handle('Clean Code', 'Robert C. Martin', '9780132350884');

        (new BorrowBookHandler($books))->handle($id);

        $stored = $books->all()[0];
        $this->assertTrue($stored->status()->isBorrowed());
    }
}
