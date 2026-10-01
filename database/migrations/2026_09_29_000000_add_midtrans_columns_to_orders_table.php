<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('total_price');
            $table->string('midtrans_order_id', 50)->nullable()->unique()->after('shipping_cost');
            $table->string('snap_token', 100)->nullable()->after('midtrans_order_id');
            $table->string('payment_status', 20)->default('pending')->index()->after('snap_token');
            $table->string('payment_type', 50)->nullable()->after('payment_status');
            $table->string('transaction_id', 100)->nullable()->after('payment_type');
            $table->timestamp('paid_at')->nullable()->after('transaction_id');
            $table->json('raw_notification')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn([
                'shipping_cost',
                'midtrans_order_id',
                'snap_token',
                'payment_status',
                'payment_type',
                'transaction_id',
                'paid_at',
                'raw_notification',
            ]);
        });
    }
};
