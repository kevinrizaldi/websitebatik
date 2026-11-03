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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->string('name');
            $table->string('nama')->nullable();
            $table->string('product_type')->nullable();
            $table->string('jenis_produk')->nullable();
            $table->string('sku')->unique();
            $table->string('category');
            $table->string('kategori')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('harga', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stok')->default(0);
            $table->string('status')->default('Tersedia');
            $table->text('description')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('material')->nullable();
            $table->string('size')->nullable();
            $table->string('ukuran')->nullable();
            $table->json('size_stock')->nullable();
            $table->json('stok_ukuran')->nullable();
            $table->string('image')->nullable();
            $table->string('gambar')->nullable();
            $table->json('other_images')->nullable();
            $table->json('gambar_lainnya')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
