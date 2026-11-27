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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'weight')) {
                $table->unsignedInteger('weight')->default(300)->after('stok');
            }
            if (! Schema::hasColumn('products', 'berat')) {
                $table->unsignedInteger('berat')->default(300)->after('weight');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('products', 'weight')) {
                $cols[] = 'weight';
            }
            if (Schema::hasColumn('products', 'berat')) {
                $cols[] = 'berat';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
