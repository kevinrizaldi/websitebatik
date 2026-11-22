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
            if (! Schema::hasColumn('addresses', 'district')) {
                $table->string('district')->nullable()->after('city_id');
            }
            if (! Schema::hasColumn('addresses', 'kecamatan')) {
                $table->string('kecamatan')->nullable()->after('district');
            }
            if (! Schema::hasColumn('addresses', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable()->after('kecamatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'district_id')) {
                $table->dropColumn('district_id');
            }
            if (Schema::hasColumn('addresses', 'kecamatan')) {
                $table->dropColumn('kecamatan');
            }
            if (Schema::hasColumn('addresses', 'district')) {
                $table->dropColumn('district');
            }
        });
    }
};
