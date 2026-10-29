<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ulasans', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['produk_id']);
            // Re-add with nullOnDelete
            $table->foreignId('produk_id')
                ->nullable()
                ->change()
                ->constrained('produks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ulasans', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->foreignId('produk_id')
                ->nullable(false)
                ->change()
                ->constrained('produks')
                ->cascadeOnDelete();
        });
    }
};