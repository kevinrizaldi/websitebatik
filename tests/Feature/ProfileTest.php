<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_route_redirects_guests_to_login(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_user_avatar_initials_use_the_first_two_name_words_or_one_for_single_word_names(): void
    {
        $user = User::factory()->create(['name' => 'raka saputra geovany']);

        $this->assertSame('RS', $user->initials);

        $user->name = 'raka';

        $this->assertSame('R', $user->initials);
    }

    public function test_account_shortcut_opens_profile_and_logout_is_separate_below_profile_navigation(): void
    {
        $user = User::factory()->create(['name' => 'raka saputra geovany']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('RS')
            ->assertDontSee('Masuk sebagai');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeInOrder(['Pesanan Saya', 'Keluar (Logout)']);
    }

    public function test_profile_avatar_uses_name_initials_instead_of_the_account_photo(): void
    {
        $user = User::factory()->create([
            'name' => 'raka saputra geovany',
            'avatar' => 'https://example.test/avatar.jpg',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('RS')
            ->assertSee('aria-label="Ubah foto profil"', false)
            ->assertDontSee('https://example.test/avatar.jpg');
    }

    public function test_user_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar' => 'https://example.test/avatar.jpg']);

        $response = $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('profile.png'),
        ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'avatar-updated');

        $avatarPath = $user->fresh()->avatar;
        $this->assertStringStartsWith('avatars/'.$user->id.'/', $avatarPath);
        $this->assertNotNull($user->fresh()->profile_photo_url);
        Storage::disk('public')->assertExists($avatarPath);
    }

    public function test_user_can_remove_a_profile_photo_and_return_to_initials(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'raka saputra']);
        $avatarPath = UploadedFile::fake()->image('profile.png')->store('avatars/'.$user->id, 'public');
        $user->update(['avatar' => $avatarPath]);

        $response = $this->actingAs($user)->delete(route('profile.avatar.destroy'));

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'avatar-removed');

        $this->assertNull($user->fresh()->avatar);
        $this->assertNull($user->fresh()->profile_photo_url);
        Storage::disk('public')->assertMissing($avatarPath);
        $this->get(route('profile.edit'))->assertOk()->assertSee('RS');
    }

    public function test_profile_photo_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->create('profile.txt', 10, 'text/plain'),
            ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }

    public function test_admin_profile_shows_the_profile_photo_upload_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'raka saputra']);

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(route('profile.avatar.update'))
            ->assertSee('aria-label="Ubah foto profil"', false)
            ->assertSee('RS');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
