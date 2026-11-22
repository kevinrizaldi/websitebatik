<?php

namespace Tests\Feature\Api;

use App\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_verify_id_token_returns_payload_when_audience_and_timestamps_are_valid(): void
    {
        config(['services.google.client_id' => 'google-client-id']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload(), 200),
        ]);

        $payload = (new GoogleAuthService)->verifyIdToken('google-id-token-valid');

        $this->assertIsArray($payload);
        $this->assertSame('google-sub-001', $payload['sub']);
        $this->assertSame('google-client-id', $payload['aud']);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), 'oauth2.googleapis.com/tokeninfo')
                && $request['id_token'] === 'google-id-token-valid';
        });
    }

    public function test_verify_id_token_fails_closed_when_google_client_id_is_unconfigured(): void
    {
        config(['services.google.client_id' => null]);

        Http::fake();

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-any'));

        Http::assertNothingSent();
    }

    public function test_verify_id_token_fails_closed_when_google_client_id_is_empty(): void
    {
        config(['services.google.client_id' => '']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload(), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-any'));
    }

    public function test_verify_id_token_rejects_audience_mismatch(): void
    {
        config(['services.google.client_id' => 'expected-client-id']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload([
                'aud' => 'other-client-id',
            ]), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-mismatched-aud'));
    }

    public function test_verify_id_token_rejects_token_without_audience(): void
    {
        config(['services.google.client_id' => 'google-client-id']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload([
                'aud' => null,
            ]), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-missing-aud'));
    }

    public function test_verify_id_token_rejects_expired_or_missing_exp_claim(): void
    {
        config(['services.google.client_id' => 'google-client-id']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload([
                'exp' => time() - 10,
            ]), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-expired'));

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload([
                'exp' => null,
            ]), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-no-exp'));
    }

    public function test_verify_id_token_rejects_iat_in_the_future_beyond_clock_skew(): void
    {
        config(['services.google.client_id' => 'google-client-id']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload([
                'iat' => time() + 3600,
            ]), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-future-iat'));
    }

    public function test_verify_id_token_rejects_nbf_in_the_future_beyond_clock_skew(): void
    {
        config(['services.google.client_id' => 'google-client-id']);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response($this->googleIdTokenPayload([
                'nbf' => time() + 3600,
            ]), 200),
        ]);

        $this->assertNull((new GoogleAuthService)->verifyIdToken('google-id-token-future-nbf'));
    }

    /**
     * Build a tokeninfo payload that would otherwise pass verification.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function googleIdTokenPayload(array $overrides = []): array
    {
        $payload = [
            'sub' => 'google-sub-001',
            'email' => 'googleuser@example.com',
            'email_verified' => true,
            'name' => 'Google User',
            'picture' => 'https://example.com/google-avatar.png',
            'aud' => 'google-client-id',
            'iat' => time() - 60,
            'exp' => time() + 600,
        ];

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($payload[$key]);

                continue;
            }

            $payload[$key] = $value;
        }

        return $payload;
    }
}
