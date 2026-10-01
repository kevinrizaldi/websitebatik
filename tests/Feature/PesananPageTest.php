<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the pesanan page renders successfully.
     */
    public function test_pesanan_page_renders_successfully(): void
    {
        $response = $this->get(route('pesanan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pesanan Saya');
        $response->assertSee('Menunggu Pembayaran');
    }

    /**
     * Test navigation contains link to pesanan across customer pages.
     */
    public function test_navigation_contains_pesanan_link(): void
    {
        $pesananUrl = route('pesanan.index');

        $this->get('/')->assertSee($pesananUrl);
        $this->get(route('koleksi.index'))->assertSee($pesananUrl);
        $this->get(route('keranjang.index'))->assertSee($pesananUrl);
    }
}
