<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Registrasi baru harus mengirim notifikasi verifikasi dan redirect ke halaman notice.
     */
    public function test_registration_sends_verification_email_and_redirects_to_notice(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'pelanggan@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmailNotification::class);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Pengguna yang belum verifikasi harus diarahkan ke halaman notice saat akses route terproteksi.
     */
    public function test_unverified_user_is_redirected_from_protected_routes(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    /**
     * Pengguna yang sudah verifikasi bisa akses dashboard admin jika admin.
     */
    public function test_verified_user_can_access_protected_routes(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    /**
     * Pengguna yang belum verifikasi tetap bisa membuka halaman profil untuk melihat / memperbaiki emailnya.
     */
    public function test_unverified_user_can_access_profile_page(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    /**
     * Klik tautan verifikasi yang valid harus memverifikasi email pengguna.
     */
    public function test_valid_verification_link_verifies_user(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /**
     * Kirim ulang email verifikasi harus mengirim notifikasi baru.
     */
    public function test_resend_verification_email_sends_new_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    /**
     * Pengguna yang sudah terverifikasi tidak perlu dikirim email ulang.
     */
    public function test_already_verified_user_is_redirected_on_resend(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        Notification::assertNothingSentTo($user);
    }

    /**
     * Login via Google harus otomatis memverifikasi email user baru maupun yang sudah ada.
     */
    public function test_google_login_marks_user_email_as_verified(): void
    {
        $existingUnverified = User::factory()->unverified()->create([
            'email' => 'google-existing@example.com',
            'google_id' => 'google-123',
        ]);

        $socialiteExisting = Mockery::mock(SocialiteUser::class);
        $socialiteExisting->shouldReceive('getId')->andReturn('google-123');
        $socialiteExisting->shouldReceive('getEmail')->andReturn('google-existing@example.com');
        $socialiteExisting->shouldReceive('getName')->andReturn('Existing Google User');
        $socialiteExisting->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $socialiteNew = Mockery::mock(SocialiteUser::class);
        $socialiteNew->shouldReceive('getId')->andReturn('google-456');
        $socialiteNew->shouldReceive('getEmail')->andReturn('google-new@example.com');
        $socialiteNew->shouldReceive('getName')->andReturn('New Google User');
        $socialiteNew->shouldReceive('getAvatar')->andReturn('https://example.com/avatar2.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->twice()
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->twice()
            ->andReturn($socialiteExisting, $socialiteNew);

        $this->get(route('auth.google.callback'))->assertRedirect('/');
        $this->assertTrue($existingUnverified->fresh()->hasVerifiedEmail());

        $this->post(route('logout'));

        $this->get(route('auth.google.callback'))->assertRedirect('/');
        $newUser = User::where('email', 'google-new@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasVerifiedEmail());
    }
}
