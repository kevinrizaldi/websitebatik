<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi Kode OTP | Hamzah Store</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FAF7F2;
            color: #26211D;
        }
        .font-serif-luxury {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-[#B58742] selection:text-white antialiased bg-[#FAF7F2] text-[#26211D]">

    <!-- Top Minimal Header -->
    <header class="w-full border-b border-[#ECE4D8] bg-[#FAF7F2]/90 backdrop-blur-md sticky top-0 z-30 px-6 sm:px-12 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-sm group-hover:bg-stone-800 transition-all duration-300">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                    </svg>
                </div>
                <span class="font-bold tracking-wider text-sm sm:text-base text-[#26211D] uppercase font-sans">
                    Hamzah Style Official
                </span>
            </a>

            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#201A17]/20 text-xs tracking-wider uppercase font-medium text-[#201A17] hover:bg-[#201A17] hover:text-white hover:border-[#201A17] transition duration-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali Masuk</span>
            </a>
        </div>
    </header>

    <!-- Main Section -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 relative overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#EEDBC5]/50 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#F5EDE2]/70 blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-white border border-[#ECE4D8] rounded-3xl p-6 sm:p-10 shadow-[0_20px_60px_-15px_rgba(32,26,23,0.06)] relative z-10">
            <!-- Icon -->
            <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#ECE4D8] flex items-center justify-center text-[#201A17] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>

            <h1 class="font-serif-luxury text-2xl font-semibold text-[#26211D] tracking-tight">
                Verifikasi Kode OTP
            </h1>
            <p class="text-xs text-stone-600 mt-2 leading-relaxed font-light">
                Kode verifikasi 6 digit telah dikirim ke email:<br>
                <span class="font-medium text-[#201A17]">{{ $email }}</span>
            </p>

            <!-- Status Alert -->
            @if (session('status'))
                <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Error Alert -->
            @if ($errors->has('otp') || $errors->has('email'))
                <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ $errors->first('otp') ?: $errors->first('email') }}</span>
                </div>
            @endif

            <!-- Form OTP -->
            <form method="POST" action="{{ route('password.otp.verify') }}" class="mt-6 space-y-6" id="otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="otp" id="otp-hidden" value="{{ old('otp') }}">

                <!-- 6 Kotak Input OTP -->
                <div>
                    <label class="block text-xs font-semibold tracking-wider text-stone-800 uppercase mb-3 text-center">
                        Masukkan 6 Digit Angka
                    </label>
                    <div class="flex items-center justify-between gap-2 sm:gap-3" id="otp-inputs">
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]"
                                data-index="{{ $i }}"
                                class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-xl sm:text-2xl font-bold bg-[#FAF7F2] border @error('otp') border-rose-400 @else border-[#ECE4D8] @enderror rounded-2xl text-[#201A17] focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition-all duration-150"
                                autocomplete="off"
                            />
                        @endfor
                    </div>
                </div>

                <!-- Expiry Countdown -->
                <div class="text-center text-xs text-stone-500 flex items-center justify-center gap-1.5" id="expire-timer-wrapper">
                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Berlaku selama: <strong id="expire-countdown" class="text-[#201A17] font-semibold">10:00</strong></span>
                </div>

                <!-- Tombol Submit -->
                <button
                    type="submit"
                    class="w-full py-3.5 px-6 rounded-xl bg-[#201A17] text-[#FAF7F2] text-sm font-semibold tracking-wider hover:bg-stone-800 active:scale-[0.99] transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 group"
                >
                    <span>Verifikasi Kode OTP</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <!-- Kirim Ulang OTP -->
            <div class="mt-6 pt-6 border-t border-[#ECE4D8] text-center">
                <p class="text-xs text-stone-600 mb-2 font-light">Tidak menerima kode verifikasi?</p>
                <form method="POST" action="{{ route('password.email') }}" id="resend-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button
                        type="submit"
                        id="resend-btn"
                        class="text-xs font-semibold text-[#B58742] hover:text-[#8D642C] transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Kirim Ulang Kode (<span id="resend-countdown">60</span>s)
                    </button>
                </form>
                <div class="mt-4">
                    <a href="{{ route('password.request') }}" class="text-xs text-stone-500 hover:text-stone-800 transition">
                        ← Ganti Alamat Email
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const boxes = document.querySelectorAll('.otp-box');
            const hiddenInput = document.getElementById('otp-hidden');
            const form = document.getElementById('otp-form');

            // Auto-focus kotak pertama
            if (boxes.length > 0) {
                boxes[0].focus();
            }

            function updateHiddenInput() {
                let code = '';
                boxes.forEach(b => code += b.value);
                hiddenInput.value = code;
                return code;
            }

            boxes.forEach((box, idx) => {
                box.addEventListener('input', function (e) {
                    const val = this.value.replace(/[^0-9]/g, '');
                    this.value = val ? val[val.length - 1] : '';

                    if (this.value && idx < boxes.length - 1) {
                        boxes[idx + 1].focus();
                    }

                    const code = updateHiddenInput();
                    if (code.length === 6) {
                        form.submit();
                    }
                });

                box.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !this.value && idx > 0) {
                        boxes[idx - 1].focus();
                        boxes[idx - 1].value = '';
                        updateHiddenInput();
                    }
                });

                box.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    const digits = pasted.replace(/[^0-9]/g, '').slice(0, 6);

                    if (digits) {
                        digits.split('').forEach((d, i) => {
                            if (boxes[i]) boxes[i].value = d;
                        });
                        const nextIdx = Math.min(digits.length, boxes.length - 1);
                        boxes[nextIdx].focus();
                        const code = updateHiddenInput();
                        if (code.length === 6) {
                            form.submit();
                        }
                    }
                });
            });

            // Timer Expired OTP (10 Menit = 600s)
            let expireSeconds = 600;
            const expireEl = document.getElementById('expire-countdown');
            const expireTimer = setInterval(() => {
                expireSeconds--;
                if (expireSeconds <= 0) {
                    clearInterval(expireTimer);
                    expireEl.textContent = 'Kadaluarsa';
                    expireEl.classList.add('text-rose-600');
                } else {
                    const m = String(Math.floor(expireSeconds / 60)).padStart(2, '0');
                    const s = String(expireSeconds % 60).padStart(2, '0');
                    expireEl.textContent = `${m}:${s}`;
                }
            }, 1000);

            // Timer Cooldown Kirim Ulang (60s)
            let resendCooldown = 60;
            const resendBtn = document.getElementById('resend-btn');
            const resendCountdown = document.getElementById('resend-countdown');
            resendBtn.disabled = true;

            const resendTimer = setInterval(() => {
                resendCooldown--;
                if (resendCooldown <= 0) {
                    clearInterval(resendTimer);
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Kirim Ulang Kode Sekarang';
                } else {
                    resendCountdown.textContent = resendCooldown;
                }
            }, 1000);
        });
    </script>
</body>
</html>
