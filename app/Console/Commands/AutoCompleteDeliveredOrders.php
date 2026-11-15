<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCompleteDeliveredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-complete {--dry-run : Menjalankan pengecekan simulasi tanpa mengubah database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menyelesaikan pesanan yang berstatus "Dikirim" berdasarkan durasi estimasi kurir (Opsi A: Instan 1 hari, Cepat 2 hari, Reguler 4 hari, Default 3 hari).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN aktif: Pengecekan dilakukan tanpa mengubah data database.');
        }

        // Ambil semua pesanan yang sedang berstatus 'Dikirim'
        $orders = Order::query()
            ->with(['shipment', 'pengiriman'])
            ->where('status', 'Dikirim')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Tidak ada pesanan berstatus "Dikirim" yang perlu diproses.');
            return Command::SUCCESS;
        }

        $now = now();
        $completedCount = 0;

        foreach ($orders as $order) {
            $courier = $order->shipment?->courier
                ?? $order->pengiriman?->ekspedisi
                ?? 'JNE Reguler';

            $thresholdDays = $this->resolveThresholdDays($order, $courier);

            // Waktu awal pengiriman: prioritaskan tanggal kirim shipment, jika kosong gunakan updated_at pesanan
            /** @var Carbon $shippedAt */
            $shippedAt = $order->shipment?->shipped_at
                ?? $order->pengiriman?->tanggal_kirim
                ?? $order->updated_at
                ?? $order->created_at;

            $autoCompleteDate = $shippedAt->copy()->addDays($thresholdDays);

            $this->line(sprintf(
                'Pesanan #%s | Kurir: %s | Batas: %d hari | Dikirim: %s | Selesai Otomatis: %s',
                $order->code,
                $courier,
                $thresholdDays,
                $shippedAt->format('d/m/Y H:i'),
                $autoCompleteDate->format('d/m/Y H:i')
            ));

            // Jika waktu sekarang sudah melewati batas waktu auto-complete
            if ($now->greaterThanOrEqualTo($autoCompleteDate)) {
                if (! $isDryRun) {
                    $this->markOrderAsCompleted($order, $courier, $thresholdDays);
                }
                $completedCount++;
                $this->info("  -> [SELESAI] Pesanan #{$order->code} telah melewati batas waktu {$thresholdDays} hari.");
            } else {
                $remainingHours = $now->diffInHours($autoCompleteDate, false);
                $this->comment("  -> [MENUNGGU] Masih dalam batas pengiriman (sisa waktu ~{$remainingHours} jam lagi).");
            }
        }

        if ($isDryRun) {
            $this->info("Simulasi selesai. Ada {$completedCount} pesanan yang memenuhi syarat untuk diselesaikan.");
        } else {
            $this->info("Proses selesai. Sebanyak {$completedCount} pesanan berhasil otomatis diubah menjadi 'Selesai'.");
        }

        return Command::SUCCESS;
    }

    /**
     * Logika Opsi A Cerdas Tingkat Lanjut:
     * 1. Deteksi angka estimasi hari jika ada (contoh: "2 hari", "2-3 hari", "4 hari").
     *    -> Rumus: (Estimasi Hari Maksimal) + 1 Hari Buffer/Toleransi.
     * 2. Jika tidak ada angka spesifik, gunakan klasifikasi nama kurir:
     *    -> Murni Instan / Sameday (GoSend, GrabExpress, Instant tanpa angka hari): 1 Hari.
     *    -> Kilat / Next Day (SiCepat BEST, JNE YES, TIKI ONS): 2 Hari (1 hari kirim + 1 hari buffer).
     *    -> Reguler (JNE Reguler, SiCepat Reg, POS, TIKI): 4 Hari (3 hari estimasi + 1 hari buffer).
     *    -> Default kurir lainnya: 3 Hari.
     */
    protected function resolveThresholdDays(Order $order, ?string $courier): int
    {
        // Gabungkan seluruh teks terkait kurir dan catatan untuk memindai estimasi hari
        $textToScan = implode(' ', array_filter([
            $courier,
            $order->shipment?->notes,
            $order->pengiriman?->catatan,
            $order->notes,
        ]));

        $lower = strtolower(trim($textToScan));

        // Prioritas 1: Deteksi rentang hari, misal "2-3 hari", "2 - 4 hari", atau "2 sd 3 hari"
        if (preg_match('/(\d+)\s*(?:[-–]|sd|sampai)\s*(\d+)\s*(?:hari|days)/i', $lower, $matches)) {
            $maxDays = (int) $matches[2];
            return max(1, $maxDays + 1);
        }

        // Prioritas 2: Deteksi angka tunggal hari, misal "2 hari", "3 hari", "4 hari"
        if (preg_match('/(\d+)\s*(?:hari|days)/i', $lower, $matches)) {
            $estDays = (int) $matches[1];
            return max(1, $estDays + 1);
        }

        // Prioritas 3: Deteksi berdasarkan nama jenis kurir jika tidak ada angka hari tertera
        $c = strtolower(trim($courier ?? ''));

        // Kurir Instan / Sameday murni (tiba hari itu juga): 1 Hari
        if (str_contains($c, 'instant') || str_contains($c, 'gosend') || str_contains($c, 'grab') || str_contains($c, 'sameday')) {
            return 1;
        }

        // Kurir Kilat / Next Day (besok tiba): 2 Hari (1 hari kirim + 1 hari toleransi)
        if (str_contains($c, 'best') || str_contains($c, 'yes') || str_contains($c, 'ons') || str_contains($c, 'next day') || str_contains($c, 'kilat')) {
            return 2;
        }

        // Kurir Reguler standar (estimasi umum 3 hari): 4 Hari (3 hari kirim + 1 hari toleransi)
        if (str_contains($c, 'reguler') || str_contains($c, 'reg') || str_contains($c, 'standar') || str_contains($c, 'jne') || str_contains($c, 'sicepat')) {
            return 4;
        }

        // Default jika jenis kurir umum/lainnya: 3 Hari
        return 3;
    }

    /**
     * Update status pesanan dan logistik menjadi Selesai / Diterima di dalam transaksi aman.
     */
    protected function markOrderAsCompleted(Order $order, string $courier, int $thresholdDays): void
    {
        DB::transaction(function () use ($order, $courier, $thresholdDays): void {
            $order->update([
                'status' => 'Selesai',
            ]);

            // Update record shipment / pengiriman
            $order->getOrInitPengiriman()->update([
                'shipping_status' => 'Diterima',
                'status_pengiriman' => 'Diterima',
            ]);

            Log::info("Auto-Complete Order: Pesanan #{$order->code} otomatis berstatus Selesai setelah {$thresholdDays} hari pengiriman ({$courier}).");
        });
    }
}
