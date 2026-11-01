<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_register_and_log_out(): void
    {
        $this->post('/register', [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->get('/')->assertSee('Анна');
        $this->get('/login')->assertRedirect('/');

        $this->post('/logout')->assertRedirect('/');
        $this->app['auth']->forgetGuards();

        $this->get('/')->assertSee('Войти')->assertDontSee('Анна');
    }

    public function test_reader_can_log_in_with_email(): void
    {
        $this->post('/register', [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);
        $this->post('/logout');
        $this->app['auth']->forgetGuards();

        $this->post('/login', [
            'email' => 'anna@example.com',
            'password' => 'secret-pass',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
    }
}
