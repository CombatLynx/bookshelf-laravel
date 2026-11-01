<?php

declare(strict_types=1);

namespace Tests\Feature\Library;

use App\Domain\Library\Clock;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\FrozenClock;
use Tests\TestCase;

final class LendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_borrow_and_return_a_book(): void
    {
        $this->post('/books', [
            'title' => 'Domain-Driven Design',
            'author' => 'Eric Evans',
            'isbn' => '978-0-321-12521-7',
        ])->assertRedirect('/');

        $id = (string) DB::table('books')->value('id');
        $reader = $this->signIn();

        $this->post('/books/' . $id . '/borrow')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'borrowed',
            'borrower_name' => 'Анна',
            'borrower_id' => $reader->id,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Анна')
            ->assertSee('Продлить');

        $this->post('/books/' . $id . '/borrow')->assertSessionHasErrors('book');

        $this->post('/books/' . $id . '/return')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'available',
            'borrower_name' => null,
            'borrower_id' => null,
            'renewals' => 0,
        ]);
    }

    public function test_loan_can_be_renewed_once_from_the_day_of_renewal(): void
    {
        $this->app->instance(Clock::class, new FrozenClock(new DateTimeImmutable('2026-10-01')));

        $id = $this->registerBook();
        $this->signIn();
        $this->post('/books/' . $id . '/borrow')->assertRedirect('/');

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
        $this->signIn();
        $this->post('/books/' . $id . '/borrow');

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

    public function test_guest_is_sent_to_login_before_borrowing(): void
    {
        $id = $this->registerBook();

        $this->post('/books/' . $id . '/borrow')->assertRedirect('/login');
        $this->assertDatabaseHas('books', ['id' => $id, 'status' => 'available']);
    }

    public function test_another_reader_cannot_return_the_book(): void
    {
        $id = $this->registerBook();
        $this->signIn('Анна');
        $this->post('/books/' . $id . '/borrow')->assertRedirect('/');

        $this->signIn('Борис');
        $this->post('/books/' . $id . '/return')->assertSessionHasErrors('book');
        $this->get('/')->assertDontSee('Вернуть');
        $this->assertDatabaseHas('books', ['id' => $id, 'status' => 'borrowed']);
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
        $this->get('/')->assertOk()->assertSee('Полка')->assertSee('Войти');
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

    private function signIn(string $name = 'Анна'): User
    {
        $reader = User::factory()->create(['name' => $name]);
        $this->actingAs($reader);

        return $reader;
    }
}
