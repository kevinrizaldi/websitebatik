<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu ulasan per customer per produk: hapus duplikat lama
     * (pertahankan entri terbaru) lalu kunci dengan unique index.
     */
    public function up(): void
    {
        $groups = DB::table('ulasans')
            ->selectRaw('user_id, produk_id, MAX(id) as keep_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id', 'produk_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $row) {
            DB::table('ulasans')
                ->where('user_id', $row->user_id)
                ->where('produk_id', $row->produk_id)
                ->where('id', '<', $row->keep_id)
                ->delete();
        }

        Schema::table('ulasans', function (Blueprint $table) {
            $table->unique(['user_id', 'produk_id'], 'ulasans_user_produk_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ulasans', function (Blueprint $table) {
            $table->dropUnique('ulasans_user_produk_unique');
        });
    }
};
