<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('sku')->unique();
            $table->string('kategori');

            $table->decimal('harga', 15, 2);

            $table->integer('stok')->default(0);

            $table->enum('status', [
                'Tersedia',
                'Stok Menipis',
                'Habis'
            ])->default('Tersedia');

            $table->string('deskripsi')->nullable();
            $table->string('material')->nullable();

            $table->string('gambar')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
