<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup Legacy Payment System
 *
 * Removes duplicated payment data that existed in two places:
 *
 * 1. Columns in `orders` that were already migrated to `payments` table
 *    (migration 2026_09_30_000001_copy_midtrans_data_from_orders_to_payments).
 *    The copy was done but the source columns were never dropped.
 *
 * 2. The `pembayarans` table (fito's legacy system) — replaced by `payments`.
 *    Data in pembayarans is NOT migrated because PaymentController.finish()
 *    already writes mock-payment records; real data lives in `payments`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Drop duplicate Midtrans columns from `orders` ──────────────────
        // These were added by: add_midtrans_columns_to_orders_table (2026_09_29)
        // and add_payment_deadline_to_orders_table (2026_09_30).
        // The data was already copied to `payments` by: copy_midtrans_data_from_orders_to_payments.
        // `payment_deadline` and `payment_status` stay — they are still used
        // by Order model constants and business logic.
        Schema::table('orders', function (Blueprint $table) {
            // Drop index before column
            $table->dropUnique('orders_midtrans_order_id_unique');

            $table->dropColumn([
                'midtrans_order_id',
                'snap_token',
                'payment_type',
                'transaction_id',
                'paid_at',
                'raw_notification',
            ]);
        });

        // ── 2. Migrate any unique data from `pembayarans` → `payments` ────────
        // Copy snap_token to orders that don't yet have a matching payments row
        // (safety net — in practice payments table should already have all data)
        $orphans = DB::table('pembayarans as p')
            ->leftJoin('payments as pay', 'pay.order_id', '=', 'p.order_id')
            ->whereNull('pay.id')
            ->whereNotNull('p.snap_token')
            ->select('p.*')
            ->get();

        foreach ($orphans as $row) {
            DB::table('payments')->insert([
                'order_id'          => $row->order_id,
                'midtrans_order_id' => 'LEGACY-' . $row->order_id . '-' . time(),
                'snap_token'        => $row->snap_token,
                'status'            => match ($row->status_pembayaran) {
                    'Berhasil'  => 'paid',
                    'Dibatalkan' => 'cancelled',
                    default     => 'pending',
                },
                'payment_type'  => $row->metode_pembayaran,
                'transaction_id'=> $row->transaction_id,
                'gross_amount'  => $row->jumlah_bayar,
                'paid_at'       => $row->tanggal_bayar,
                'created_at'    => $row->created_at,
                'updated_at'    => $row->updated_at,
            ]);
        }

        // ── 3. Drop `pembayarans` table ────────────────────────────────────────
        Schema::dropIfExists('pembayarans');
    }

    public function down(): void
    {
        // Recreate pembayarans table
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('metode_pembayaran')->nullable();
            $table->string('status_pembayaran')->default('Menunggu Pembayaran');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->timestamp('tanggal_bayar')->nullable();
            $table->string('snap_token')->nullable();
            $table->text('snap_redirect_url')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('payment_response')->nullable();
            $table->timestamps();
        });

        // Restore dropped columns to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('midtrans_order_id', 50)->nullable()->unique()->after('shipping_cost');
            $table->string('snap_token', 100)->nullable()->after('midtrans_order_id');
            $table->string('payment_type', 50)->nullable()->after('payment_status');
            $table->string('transaction_id', 100)->nullable()->after('payment_type');
            $table->timestamp('paid_at')->nullable()->after('transaction_id');
            $table->json('raw_notification')->nullable()->after('paid_at');
        });
    }
};
