<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko')->default('Hamzah Style Official');
            $table->string('kota_asal')->default('Kota Surakarta (Solo)');
            $table->string('provinsi_asal')->default('Jawa Tengah');
            $table->string('kode_pos_asal')->default('57141');
            $table->text('alamat_asal')->nullable();
            $table->string('no_telepon_toko')->nullable();
            $table->string('email_toko')->nullable();
            $table->text('deskripsi_toko')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
