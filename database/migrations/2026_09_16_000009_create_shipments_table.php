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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('courier')->default('JNE Reguler');
            $table->string('ekspedisi')->default('JNE Reguler');
            $table->string('tracking_number')->nullable();
            $table->string('no_resi')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('tanggal_kirim')->nullable();
            $table->string('shipping_status')->default('Menunggu Pengiriman');
            $table->string('status_pengiriman')->default('Menunggu Pengiriman');
            $table->text('notes')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
