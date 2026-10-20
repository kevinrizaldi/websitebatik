<?php

namespace Tests\Feature\Admin;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KategoriManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_kategori_index_page(): void
    {
        $admin = $this->createAdmin();
        Kategori::create(['nama_kategori' => 'Kain Batik', 'slug' => 'kain-batik', 'deskripsi' => 'Kain mori batik halus']);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index'));

        $response->assertOk()
            ->assertSee('Kelola Kategori Produk')
            ->assertSee('Kain Batik')
            ->assertSee('Kain mori batik halus');
    }

    public function test_admin_can_create_kategori_with_image_upload(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('seragam.jpg', 600, 800);

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Seragam Batik Modern',
            'deskripsi' => 'Koleksi seragam kantor premium.',
            'gambar' => $file,
        ]);

        $response->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $kategori = Kategori::where('nama_kategori', 'Seragam Batik Modern')->first();
        $this->assertNotNull($kategori);
        $this->assertNotNull($kategori->gambar);
        $this->assertTrue(Storage::disk('public')->exists($kategori->gambar));
    }

    public function test_admin_can_update_kategori_and_replace_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $oldFile = UploadedFile::fake()->image('old_kain.jpg');
        $oldPath = $oldFile->store('kategori', 'public');

        $kategori = Kategori::create([
            'nama_kategori' => 'Kain Jarik',
            'slug' => 'kain-jarik',
            'deskripsi' => 'Deskripsi lama',
            'gambar' => $oldPath,
        ]);

        $newFile = UploadedFile::fake()->image('new_kain.jpg');

        $response = $this->actingAs($admin)->put(route('admin.kategori.update', $kategori), [
            'nama_kategori' => 'Kain Jarik Tradisional',
            'deskripsi' => 'Deskripsi baru',
            'gambar' => $newFile,
        ]);

        $response->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $kategori->refresh();
        $this->assertSame('Kain Jarik Tradisional', $kategori->nama_kategori);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($kategori->gambar));
    }

    public function test_admin_can_remove_kategori_custom_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('foto.jpg');
        $path = $file->store('kategori', 'public');

        $kategori = Kategori::create([
            'nama_kategori' => 'Baju Formal',
            'slug' => 'baju-formal',
            'gambar' => $path,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.kategori.update', $kategori), [
            'nama_kategori' => 'Baju Formal',
            'hapus_gambar' => '1',
        ]);

        $response->assertRedirect(route('admin.kategori.index'));

        $kategori->refresh();
        $this->assertNull($kategori->gambar);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_admin_delete_kategori_also_deletes_image_file(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('hapus.jpg');
        $path = $file->store('kategori', 'public');

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Sementara',
            'slug' => 'kategori-sementara',
            'gambar' => $path,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.kategori.destroy', $kategori));

        $response->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_welcome_page_renders_categories_from_database(): void
    {
        Kategori::create([
            'nama_kategori' => 'Batik Tulis Eksklusif',
            'slug' => 'batik-tulis-eksklusif',
            'deskripsi' => 'Karya istimewa pembatik maestro.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Jelajahi Koleksi')
            ->assertSee('Batik Tulis Eksklusif')
            ->assertSee('Karya istimewa pembatik maestro.');
    }
}
