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
            if (! Schema::hasColumn('addresses', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('kode_pos');
            }
            if (! Schema::hasColumn('addresses', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('addresses', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
