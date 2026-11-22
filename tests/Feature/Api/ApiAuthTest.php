<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_data_wrapper_with_user_and_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email'], 'token'],
                'token',
                'user',
            ])
            ->assertJsonPath('data.user.email', 'budi@example.com');

        $json = $response->json();
        $this->assertNotEmpty($json['data']['token']);
        $this->assertSame($json['data']['token'], $json['token']);
        $this->assertSame($json['data']['user']['id'], $json['user']['id']);
    }

    public function test_register_with_duplicate_email_fails_with_422_errors_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Duplikat User',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }

    public function test_login_returns_data_wrapper_with_user_and_token(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email'], 'token'],
                'token',
                'user',
            ])
            ->assertJsonPath('data.user.id', $user->id);

        $json = $response->json();
        $this->assertNotEmpty($json['data']['token']);
        $this->assertSame($json['data']['token'], $json['token']);
        $this->assertSame($user->id, $json['user']['id']);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['email' => 'wrong@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'bukan-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonPath('message', 'Email atau kata sandi salah.');
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_data_user_wrapper(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email', 'phone', 'role']],
                'user',
            ])
            ->assertJsonPath('data.user.id', $user->id);

        $json = $response->json();
        $this->assertSame($user->id, $json['user']['id']);
    }

    public function test_user_endpoint_is_alias_of_me(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email', 'phone', 'role']],
                'user',
            ])
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_logout_returns_204_and_invalidates_token(): void
    {
        $user = User::factory()->create();
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $login->json('data.token');

        $logout = $this->withToken($token)->postJson('/api/logout');
        $logout->assertStatus(204);
        $this->assertSame('', $logout->getContent());
        $this->assertSame(0, $user->fresh()->tokens()->count());

        // The feature-test app instance is reused across calls, so Sanctum's
        // RequestGuard would otherwise keep serving the user resolved during
        // the logout request. Flush so the next call re-resolves the token.
        $this->app->make('auth')->forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }

    public function test_update_profile_returns_data_user_wrapper(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/api/user', [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'phone' => '081234567890',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email', 'phone', 'role']],
                'user',
            ])
            ->assertJsonPath('data.user.name', 'Nama Baru');

        $json = $response->json();
        $this->assertSame('Nama Baru', $json['user']['name']);
    }
}
