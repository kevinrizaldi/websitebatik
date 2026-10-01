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
        Schema::create('alamats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label_alamat')->default('Rumah');
            $table->string('penerima');
            $table->string('no_telepon');
            $table->text('alamat_lengkap');
            $table->string('kota');
            $table->string('provinsi')->nullable();
            $table->string('kode_pos')->nullable();
            $table->boolean('is_utama')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamats');
    }
};
