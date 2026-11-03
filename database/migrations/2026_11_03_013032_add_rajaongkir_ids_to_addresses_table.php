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
        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable()->after('provinsi');
            }
            if (! Schema::hasColumn('addresses', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('province_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'city_id')) {
                $table->dropColumn('city_id');
            }
            if (Schema::hasColumn('addresses', 'province_id')) {
                $table->dropColumn('province_id');
            }
        });
    }
};
