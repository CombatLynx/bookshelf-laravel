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

final class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserved_book_is_held_for_the_next_reader(): void
    {
        $this->app->instance(Clock::class, new FrozenClock(new DateTimeImmutable('2026-10-10')));

        $id = $this->registerBook();
        $anna = $this->signIn('Анна');
        $this->post('/books/' . $id . '/borrow')->assertRedirect('/');

        $boris = $this->signIn('Борис');
        $this->post('/books/' . $id . '/reserve')->assertRedirect('/');
        $this->get('/')->assertSee('Борис ждёт возврата')->assertSee('Снять бронь');

        $this->actingAs($anna);
        $this->post('/books/' . $id . '/return')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'reserved',
            'borrower_id' => null,
            'reservation_reader_id' => $boris->id,
            'reservation_expires_on' => '2026-10-13',
        ]);

        $this->post('/books/' . $id . '/borrow')->assertSessionHasErrors('book');

        $this->actingAs($boris);
        $this->post('/books/' . $id . '/borrow')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'borrowed',
            'borrower_id' => $boris->id,
            'reservation_reader_id' => null,
        ]);
    }

    public function test_reader_can_cancel_a_reservation(): void
    {
        $id = $this->registerBook();
        $anna = $this->signIn('Анна');
        $this->post('/books/' . $id . '/borrow');

        $this->signIn('Борис');
        $this->post('/books/' . $id . '/reserve')->assertRedirect('/');
        $this->post('/books/' . $id . '/reservation/cancel')->assertRedirect('/');

        $this->actingAs($anna);
        $this->post('/books/' . $id . '/return')->assertRedirect('/');
        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'available',
            'reservation_reader_id' => null,
        ]);
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

    private function signIn(string $name): User
    {
        $reader = User::factory()->create(['name' => $name]);
        $this->actingAs($reader);

        return $reader;
    }
}
