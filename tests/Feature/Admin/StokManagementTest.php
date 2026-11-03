<?php

namespace Tests\Feature\Admin;

use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_stok_management_page(): void
    {
        Produk::create([
            'nama' => 'Kemeja Batik Parang Seling',
            'sku' => 'HSO-BJK-001',
            'kategori' => 'Baju Batik',
            'harga' => 385000,
            'stok' => 14,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.stok.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Stok &amp; Inventaris Sirkular', false);
        $response->assertSee('Kemeja Batik Parang Seling');
        $response->assertSee('HSO-BJK-001');
    }

    public function test_non_admin_cannot_access_stok_management(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get(route('admin.stok.index'));
        $response->assertStatus(403);
    }
}
