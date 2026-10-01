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
        Schema::table('produks', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('kategori_id')->nullable()->after('admin_id')->constrained('kategoris')->nullOnDelete();
            $table->string('jenis_produk')->nullable()->after('nama');
            $table->string('ukuran')->nullable()->after('material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['kategori_id']);
            $table->dropColumn(['admin_id', 'kategori_id', 'jenis_produk', 'ukuran']);
        });
    }
};
