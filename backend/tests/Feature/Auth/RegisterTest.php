<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Teszt Elek',
            'email' => 'teszt@example.com',
            'phone' => '+36301234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Sikeres regisztráció.')
            ->assertJsonPath('user.email', 'teszt@example.com')
            ->assertJsonPath('user.role', 'CUSTOMER')
            ->assertJsonPath('user.active', true)
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', [
            'email' => 'teszt@example.com',
            'role' => 'CUSTOMER',
            'active' => true,
        ]);
    }

    public function test_email_must_be_valid(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Teszt Elek',
            'email' => 'nem-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'teszt@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Másik Felhasználó',
            'email' => 'teszt@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_password_must_be_at_least_eight_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Teszt Elek',
            'email' => 'teszt@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_password_must_be_confirmed(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Teszt Elek',
            'email' => 'teszt@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}