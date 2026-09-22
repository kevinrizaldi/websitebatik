<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique(); // ID Pesanan / Kode Unik (misal: ORD-20260922-001)
            $table->string('customer_name');
            $table->text('address');
            $table->string('phone');
            $table->decimal('total_price', 12, 2);
            $table->string('payment_method')->default('Transfer Bank');
            $table->string('payment_proof')->nullable(); // Foto bukti pembayaran
            $table->string('status')->default('Belum Dibayar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
