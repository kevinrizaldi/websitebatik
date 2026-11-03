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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('customer_name');
            $table->text('address');
            $table->string('phone');
            $table->decimal('total_price', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->string('midtrans_order_id', 50)->nullable()->unique();
            $table->string('snap_token', 100)->nullable();
            $table->string('payment_status', 20)->default('pending')->index();
            $table->string('payment_type', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('payment_deadline')->nullable()->index();
            $table->json('raw_notification')->nullable();
            $table->string('payment_method')->default('Transfer Bank');
            $table->string('payment_proof')->nullable();
            $table->string('status')->default('Belum Dibayar');
            $table->string('tracking_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
