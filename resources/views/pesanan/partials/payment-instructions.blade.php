{{--
    Partial: instruksi pembayaran tersimpan (VA / QRIS / lainnya)
    Variables: $payment (Payment model)
--}}
@php
    $details = $payment->payment_details ?? [];
    $type    = $payment->payment_type ?? '';

    // VA Banks (bank_transfer: bca, bni, bri, mandiri, permata)
    $vaNumbers = $details['va_numbers'] ?? [];

    // Mandiri Bill Payment
    $billKey    = $details['bill_key'] ?? null;
    $billerCode = $details['biller_code'] ?? null;

    // Permata VA (separate field on some responses)
    $permataVa = $details['permata_va_number'] ?? null;

    // QRIS / GoPay
    $qrString  = $details['qr_string'] ?? null;
    $actions   = $details['actions'] ?? [];

    // Expiry time from Midtrans (use our expires_at as fallback)
    $expiryTime = $details['expiry_time']
        ?? ($payment->expires_at?->format('Y-m-d H:i:s'));
@endphp

<div class="border border-stone-100 rounded-xl overflow-hidden">
    <div class="px-4 py-2.5 bg-stone-50 border-b border-stone-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-500">INSTRUKSI PEMBAYARAN</span>
        </div>
        @if ($type)
            <span class="bg-stone-200 text-stone-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                {{ str_replace('_', ' ', $type) }}
            </span>
        @endif
    </div>

    <div class="p-4 space-y-3">

        {{-- ── VA Numbers (bank_transfer) ──────────────────────────────── --}}
        @if (!empty($vaNumbers))
            @foreach ($vaNumbers as $va)
                @if (isset($va['bank'], $va['va_number']))
                    <div class="bg-white border border-stone-100 rounded-xl p-3.5">
                        <div class="text-[10px] text-stone-400 font-bold uppercase tracking-wider mb-1">
                            NOMOR VIRTUAL ACCOUNT — {{ strtoupper($va['bank']) }}
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-base font-extrabold text-stone-900 tracking-widest">
                                {{ $va['va_number'] }}
                            </span>
                            <button
                                onclick="copyText('{{ $va['va_number'] }}', this)"
                                class="text-[11px] font-bold text-stone-600 hover:text-stone-900 border border-stone-200 px-2.5 py-1 rounded-lg transition hover:bg-stone-50">
                                Salin
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- ── Permata VA ───────────────────────────────────────────────── --}}
        @if ($permataVa)
            <div class="bg-white border border-stone-100 rounded-xl p-3.5">
                <div class="text-[10px] text-stone-400 font-bold uppercase tracking-wider mb-1">
                    NOMOR VIRTUAL ACCOUNT — PERMATA
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="font-mono text-base font-extrabold text-stone-900 tracking-widest">
                        {{ $permataVa }}
                    </span>
                    <button
                        onclick="copyText('{{ $permataVa }}', this)"
                        class="text-[11px] font-bold text-stone-600 hover:text-stone-900 border border-stone-200 px-2.5 py-1 rounded-lg transition hover:bg-stone-50">
                        Salin
                    </button>
                </div>
            </div>
        @endif

        {{-- ── Mandiri Bill Payment ──────────────────────────────────────── --}}
        @if ($billKey && $billerCode)
            <div class="bg-white border border-stone-100 rounded-xl p-3.5 space-y-2">
                <div class="text-[10px] text-stone-400 font-bold uppercase tracking-wider">
                    MANDIRI BILL PAYMENT
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-stone-400">Biller Code</div>
                        <span class="font-mono text-sm font-extrabold text-stone-900">{{ $billerCode }}</span>
                    </div>
                    <button onclick="copyText('{{ $billerCode }}', this)"
                        class="text-[11px] font-bold text-stone-600 hover:text-stone-900 border border-stone-200 px-2.5 py-1 rounded-lg transition hover:bg-stone-50">Salin</button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-stone-400">Bill Key</div>
                        <span class="font-mono text-sm font-extrabold text-stone-900">{{ $billKey }}</span>
                    </div>
                    <button onclick="copyText('{{ $billKey }}', this)"
                        class="text-[11px] font-bold text-stone-600 hover:text-stone-900 border border-stone-200 px-2.5 py-1 rounded-lg transition hover:bg-stone-50">Salin</button>
                </div>
            </div>
        @endif

        {{-- ── QRIS / GoPay: actions-based URL ──────────────────────────── --}}
        @php
            $qrImageUrl = null;
            foreach ($actions as $action) {
                if (is_array($action) && isset($action['name'], $action['url'])
                    && str_contains(strtolower($action['name']), 'qr')) {
                    $qrImageUrl = $action['url'];
                    break;
                }
            }
        @endphp
        @if ($qrImageUrl || $qrString)
            <div class="bg-white border border-stone-100 rounded-xl p-4 flex flex-col items-center gap-3">
                <div class="text-[10px] text-stone-400 font-bold uppercase tracking-wider self-start">
                    QR CODE PEMBAYARAN
                </div>
                @if ($qrImageUrl)
                    {{-- Gunakan route proxy QR agar domain Midtrans tidak diekspos ke client --}}
                    <img
                        src="{{ route('payment.qr', $payment->order_id) }}"
                        alt="QR Code Pembayaran"
                        class="w-40 h-40 object-contain border border-stone-100 rounded-xl shadow-sm">
                @endif
                @if ($qrString)
                    <div class="text-[10px] text-stone-400 mt-1 break-all text-center max-w-xs">
                        {{ $qrString }}
                    </div>
                @endif
            </div>
        @endif

        {{-- ── Tidak ada instruksi ───────────────────────────────────────── --}}
        @if (empty($vaNumbers) && !$permataVa && !$billKey && !$qrImageUrl && !$qrString)
            <p class="text-[11px] text-gray-400 italic text-center py-2">
                Instruksi pembayaran belum tersedia. Buka popup untuk memilih metode.
            </p>
        @endif

        {{-- ── Batas Waktu dari Midtrans ────────────────────────────────── --}}
        @if ($expiryTime)
            <div class="flex items-center gap-2 pt-1 text-[11px] text-stone-500 border-t border-stone-100 mt-2">
                <span>⏰</span>
                <span>Bayar sebelum: <strong>{{ \Carbon\Carbon::parse($expiryTime)->format('d M Y, H:i') }} WIB</strong></span>
            </div>
        @endif
    </div>
</div>

<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Tersalin';
        setTimeout(() => { btn.textContent = orig; }, 2000);
    });
}
</script>
