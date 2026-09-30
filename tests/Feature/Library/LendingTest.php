<?php

declare(strict_types=1);

namespace Tests\Feature\Library;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        $this->post('/books/' . $id . '/borrow')->assertRedirect('/');
        $this->assertDatabaseHas('books', ['id' => $id, 'status' => 'borrowed']);

        $this->post('/books/' . $id . '/borrow')->assertSessionHasErrors('book');

        $this->post('/books/' . $id . '/return')->assertRedirect('/');
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
}
