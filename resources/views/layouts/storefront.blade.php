<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' — HAMZAH STYLE OFFICIAL' : 'HAMZAH STYLE OFFICIAL — Warisan Batik Nusantara Modern' }}</title>
    <meta name="description" content="{{ $description ?? 'Koleksi busana batik modern dan autentik Indonesia karya pengrajin lokal pilihan. Elegan dalam setiap motif untuk momen berharga Anda.' }}">

    <!-- Google Fonts: Playfair Display (Serif) & Plus Jakarta Sans (Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF7F2;
            color: #26211D;
            -webkit-font-smoothing: antialiased;
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{ $extraHead ?? '' }}
</head>
<body class="antialiased bg-[#FAF7F2] text-[#26211D] min-h-screen flex flex-col selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          userMenu: false,
          cartCount: {{ $cartCount ?? 0 }},
          toastMessage: '{{ addslashes(session('success') ?? session('status') ?? session('info') ?? session('error') ?? '') }}',
          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3500);
          }
      }"
      x-init="if (toastMessage) { setTimeout(() => { toastMessage = ''; }, 3500); };
              window.addEventListener('show-toast', e => showToast(e.detail));
              window.addEventListener('cart-updated', e => { if (e.detail?.count !== undefined) cartCount = e.detail.count; });">

    <!-- Toast Notification -->
    <div x-cloak x-show="toastMessage"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-stone-700 max-w-md">
        <svg class="w-5 h-5 text-[#E5C38E] shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
        <button @click="toastMessage = ''" class="ml-auto text-stone-400 hover:text-white">&times;</button>
    </div>

    <!-- ================= NAVBAR ================= -->
    <x-storefront-navbar :cart-count="$cartCount ?? null" />

    <!-- ================= MAIN CONTENT ================= -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- ================= FOOTER ================= -->
    <x-storefront-footer />

    {{ $extraBody ?? '' }}

</body>
</html>

