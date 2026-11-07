<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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
     * Pengguna yang sudah verifikasi tidak diarahkan ke halaman notice saat akses route terproteksi.
     */
    public function test_verified_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Route profile hanya dapat diakses user terautentikasi dan terverifikasi (bukan admin-only)
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
}
