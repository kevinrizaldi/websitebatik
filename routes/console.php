<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal otomatis membatalkan pesanan yang melewati batas pembayaran 24 jam
Schedule::command('orders:expire-overdue')->everyFifteenMinutes();

// Jadwal otomatis menyelesaikan pesanan yang berstatus Dikirim berdasarkan durasi kurir (Opsi A)
Schedule::command('orders:auto-complete')->hourly();
