<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'teszt@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teszt@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Sikeres bejelentkezés.')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'teszt@example.com')
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'teszt@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teszt@example.com',
            'password' => 'hibas-jelszo',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'teszt@example.com',
            'password' => Hash::make('password123'),
            'active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teszt@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_authenticated_user_can_get_current_user(): void
    {
        $user = User::factory()->create([
            'active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_guest_cannot_get_current_user(): void
    {
        $this->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'active' => true,
        ]);

        $token = $user->createToken('test-token');

        $response = $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/logout');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Sikeres kijelentkezés.');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}