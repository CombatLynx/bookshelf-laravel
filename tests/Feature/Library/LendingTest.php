<?php

declare(strict_types=1);

namespace Tests\Feature\Library;

use App\Domain\Library\Clock;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\FrozenClock;
use Tests\TestCase;

final class LendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_register_borrow_and_return_a_book(): void
    {
        $this->post('/books', [
            'title' => 'Domain-Driven Design',
            'author' => 'Eric Evans',
            'isbn' => '978-0-321-12521-7',
        ])->assertRedirect('/');

        $id = DB::table('books')->value('id');

        $this->assertDatabaseHas('books', [
            'id' => $id,
            'isbn' => '9780321125217',
            'status' => 'available',
        ]);

        $this->post('/books/' . $id . '/borrow', ['borrower' => 'Анна'])->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'borrowed',
            'borrower_name' => 'Анна',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Анна')
            ->assertSee('Продлить');

        $this->post('/books/' . $id . '/borrow', ['borrower' => 'Борис'])->assertSessionHasErrors('book');

        $this->post('/books/' . $id . '/return')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'available',
            'borrower_name' => null,
            'renewals' => 0,
        ]);
    }

    public function test_loan_can_be_renewed_once_from_the_day_of_renewal(): void
    {
        $this->app->instance(Clock::class, new FrozenClock(new DateTimeImmutable('2026-10-01')));

        $id = $this->registerBook();
        $this->post('/books/' . $id . '/borrow', ['borrower' => 'Анна'])->assertRedirect('/');

        $this->assertDatabaseHas('books', [
            'id' => $id,
            'due_on' => '2026-10-15',
            'renewals' => 0,
        ]);

        $this->app->instance(Clock::class, new FrozenClock(new DateTimeImmutable('2026-10-10')));
        $this->post('/books/' . $id . '/renew')->assertRedirect('/');

        $this->assertDatabaseHas('books', [
            'id' => $id,
            'due_on' => '2026-10-24',
            'renewals' => 1,
        ]);

        $this->post('/books/' . $id . '/renew')->assertSessionHasErrors('book');
        $this->get('/')->assertSee('24.10.2026')->assertDontSee('Продлить');
    }

    public function test_overdue_loan_is_shown_and_cannot_be_renewed(): void
    {
        $this->app->instance(Clock::class, new FrozenClock(new DateTimeImmutable('2026-10-01')));

        $id = $this->registerBook();
        $this->post('/books/' . $id . '/borrow', ['borrower' => 'Анна']);

        $this->app->instance(Clock::class, new FrozenClock(new DateTimeImmutable('2026-10-20')));

        $this->get('/')->assertSee('Просрочена')->assertDontSee('Продлить');
        $this->post('/books/' . $id . '/renew')->assertSessionHasErrors('book');

        $this->post('/books/' . $id . '/return')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'available',
            'borrower_name' => null,
        ]);
    }

    public function test_borrow_requires_a_reader_name(): void
    {
        $id = $this->registerBook();

        $this->from('/')
            ->post('/books/' . $id . '/borrow', ['borrower' => ''])
            ->assertRedirect('/')
            ->assertSessionHasErrors('borrower');

        $this->assertDatabaseHas('books', ['id' => $id, 'status' => 'available']);
    }

    public function test_invalid_isbn_stays_on_the_form(): void
    {
        $this->from('/')
            ->post('/books', [
                'title' => 'Черновик',
                'author' => 'Автор',
                'isbn' => '9780321125215',
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('isbn');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_catalog_page_is_available(): void
    {
        $this->get('/')->assertOk()->assertSee('Полка');
    }

    private function registerBook(): string
    {
        $this->post('/books', [
            'title' => 'Domain-Driven Design',
            'author' => 'Eric Evans',
            'isbn' => '978-0-321-12521-7',
        ]);

        return (string) DB::table('books')->value('id');
    }
}
