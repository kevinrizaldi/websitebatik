<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RajaOngkirConfigSecurityTest extends TestCase
{
    public function test_rajaongkir_api_key_has_no_hardcoded_default(): void
    {
        $contents = File::get(config_path('rajaongkir.php'));

        $matched = preg_match(
            "/env\(\s*'RAJAONGKIR_API_KEY'\s*,\s*'([^']*)'\s*\)/",
            $contents,
            $matches
        );

        $this->assertSame(
            1,
            $matched,
            'config/rajaongkir.php must read RAJAONGKIR_API_KEY via env()'
        );

        $default = $matches[1];

        $this->assertSame(
            '',
            $default,
            'RAJAONGKIR_API_KEY config default must be empty — no hardcoded API key'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/[A-Za-z0-9]{32}/',
            $default,
            'RAJAONGKIR_API_KEY config default must not contain a 32-character key literal'
        );
    }
}
