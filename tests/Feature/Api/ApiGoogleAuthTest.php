<?php

namespace Tests\Feature\Api;

use App\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ApiGoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_with_valid_token_returns_data_wrapper_with_user_and_token(): void
    {
        $this->partialMock(GoogleAuthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verifyIdToken')
                ->once()
                ->with('google-id-token-valid')
                ->andReturn([
                    'sub' => 'google-sub-001',
                    'email' => 'googleuser@example.com',
                    'email_verified' => true,
                    'name' => 'Google User',
                    'picture' => 'https://example.com/google-avatar.png',
                ]);
        });

        $response = $this->postJson('/api/auth/google', [
            'id_token' => 'google-id-token-valid',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['user' => ['id', 'name', 'email', 'phone', 'avatar', 'role'], 'token'],
                'token',
                'user',
            ])
            ->assertJsonPath('data.user.email', 'googleuser@example.com');

        $json = $response->json();
        $this->assertNotEmpty($json['data']['token']);
        $this->assertSame($json['data']['token'], $json['token']);
        $this->assertSame($json['data']['user']['id'], $json['user']['id']);

        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'google_id' => 'google-sub-001',
        ]);
    }

    public function test_google_login_with_invalid_token_fails_with_401(): void
    {
        $this->partialMock(GoogleAuthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verifyIdToken')
                ->once()
                ->with('google-id-token-invalid')
                ->andReturn(null);
        });

        $response = $this->postJson('/api/auth/google', [
            'id_token' => 'google-id-token-invalid',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonPath('message', 'Token Google tidak valid atau sudah kadaluarsa.');

        $this->assertDatabaseMissing('users', ['email' => 'googleuser@example.com']);
    }

    public function test_google_login_with_missing_token_fails_with_401(): void
    {
        $response = $this->postJson('/api/auth/google', []);

        $response->assertStatus(401)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonPath('message', 'Token Google tidak valid atau sudah kadaluarsa.');
    }
}
