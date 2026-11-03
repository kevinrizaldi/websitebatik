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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label')->default('Rumah');
            $table->string('label_alamat')->default('Rumah');
            $table->string('recipient_name')->nullable();
            $table->string('penerima')->nullable();
            $table->string('phone')->nullable();
            $table->string('no_telepon')->nullable();
            $table->text('full_address')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('city')->nullable();
            $table->string('kota')->nullable();
            $table->string('province')->nullable();
            $table->string('provinsi')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('kode_pos')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_utama')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
