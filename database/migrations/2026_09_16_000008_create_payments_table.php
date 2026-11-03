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
        if (Schema::hasTable('payments')) {
            return;
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('midtrans_order_id', 50)->nullable()->unique();
            $table->string('snap_token', 100)->nullable();
            $table->text('snap_redirect_url')->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->string('status_pembayaran', 50)->default('Menunggu Pembayaran');
            $table->string('payment_type', 50)->nullable();
            $table->string('metode_pembayaran', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->json('payment_details')->nullable();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('jumlah_bayar', 12, 2)->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('tanggal_bayar')->nullable();
            $table->json('raw_notification')->nullable();
            $table->json('payment_response')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
