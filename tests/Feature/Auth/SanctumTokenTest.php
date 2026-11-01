<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SanctumTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_client_borrows_with_a_bearer_token(): void
    {
        $this->post('/books', [
            'title' => 'Domain-Driven Design',
            'author' => 'Eric Evans',
            'isbn' => '978-0-321-12521-7',
        ]);
        $id = (string) DB::table('books')->value('id');

        $reader = User::factory()->create([
            'name' => 'Анна',
            'email' => 'anna@example.com',
        ]);

        $token = $this->postJson('/api/token', [
            'email' => 'anna@example.com',
            'password' => 'password',
            'device_name' => 'tests',
        ])->assertOk()->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/books/' . $id . '/borrow')
            ->assertOk()
            ->assertJsonFragment(['message' => 'Книга выдана на две недели.']);

        $this->assertDatabaseHas('books', [
            'id' => $id,
            'status' => 'borrowed',
            'borrower_id' => $reader->id,
        ]);

        $other = User::factory()->create();
        $otherToken = $other->createToken('other')->plainTextToken;

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer ' . $otherToken)
            ->postJson('/api/books/' . $id . '/return')
            ->assertStatus(422);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/token')
            ->assertOk();

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/books/' . $id . '/return')
            ->assertUnauthorized();
    }

    public function test_api_without_a_token_is_rejected(): void
    {
        $this->postJson('/api/books/11111111-1111-4111-8111-111111111111/borrow')
            ->assertUnauthorized();
    }
}
