<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ulasan terikat ke pembelian (order): satu ulasan per produk per order.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('ulasans', 'order_id')) {
            Schema::table('ulasans', function (Blueprint $table) {
                $table->foreignId('order_id')->nullable()->after('produk_id')->constrained('orders')->nullOnDelete();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            // FK user_id menumpang pada unique (user_id, produk_id) sebagai
            // index-nya: lepas FK dulu, drop unique, pasang lagi FK-nya.
            Schema::table('ulasans', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        $this->dropIndexIfExists('ulasans', 'ulasans_user_produk_unique');

        if (DB::getDriverName() === 'mysql') {
            Schema::table('ulasans', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! $this->indexExists('ulasans', 'ulasans_order_produk_unique')) {
            Schema::table('ulasans', function (Blueprint $table) {
                $table->unique(['order_id', 'produk_id'], 'ulasans_order_produk_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('ulasans', 'ulasans_order_produk_unique');

        Schema::table('ulasans', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });

        if (! $this->indexExists('ulasans', 'ulasans_user_produk_unique')) {
            Schema::table('ulasans', function (Blueprint $table) {
                $table->unique(['user_id', 'produk_id'], 'ulasans_user_produk_unique');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        foreach (Schema::getIndexes($table) as $idx) {
            if (($idx['name'] ?? null) === $index) {
                return true;
            }
        }

        return false;
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS "'.$index.'"');

            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index) {
            $table->dropUnique($index);
        });
    }
};
