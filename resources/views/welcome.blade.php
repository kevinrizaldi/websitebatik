<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hamzah Style Official - Warisan Batik Nusantara Modern</title>
    <meta name="description" content="Koleksi busana batik modern dan autentik Indonesia karya pengrajin lokal pilihan. Elegan dalam setiap motif untuk momen berharga Anda.">

    <!-- Google Fonts -->
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
</head>
<body class="antialiased bg-[#FAF7F2] text-[#26211D] min-h-screen selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          activeCategory: 'Semua',
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          quickViewModal: false,
          selectedProduct: null,
          toastMessage: '',
          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3000);
          },
          openQuickView(prod) {
              this.selectedProduct = prod;
              this.quickViewModal = true;
          }
      }">

    <!-- Toast Notification -->
    <div x-cloak x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-stone-700">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>

    <!-- ================= NAVBAR ================= -->
    <x-storefront-navbar />

    <!-- ================= HERO SECTION ================= -->
    <section class="relative overflow-hidden pt-8 pb-16 md:pt-14 md:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Headline & Subtitle -->
                <div class="lg:col-span-6 lg:pr-6">
                    <!-- Pill Tag -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#EFE8DD] border border-[#DFCDBB] text-[#785933] text-xs font-semibold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-[#B58742]"></span>
                        <span>KOLEKSI EKSKLUSIF EDISI TERBATAS</span>
                    </div>

                    <!-- Heading -->
                    <h1 class="font-serif-title text-4xl sm:text-5xl lg:text-[62px] font-bold text-[#1F1916] leading-[1.12] tracking-tight mb-6">
                        Elegan dalam<br>
                        Setiap Motif
                    </h1>

                    <!-- Paragraph -->
                    <p class="text-stone-600 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
                        Temukan keindahan batik autentik Indonesia pilihan Hamzah Style Official. Menggabungkan kemewahan tradisi Nusantara dengan sentuhan modern ramah lingkungan.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('koleksi.index') }}" 
                           class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-[#201A17] text-white font-medium hover:bg-stone-800 transition duration-200 shadow-md hover:shadow-lg text-sm sm:text-base">
                            Belanja Sekarang
                        </a>
                        <a href="#koleksi" 
                           class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-white text-stone-800 border border-stone-300 font-medium hover:bg-stone-50 hover:border-stone-400 transition duration-200 text-sm sm:text-base shadow-xs">
                            Lihat Koleksi
                        </a>
                    </div>
                </div>

                <!-- Right Column: Featured Image with Overlay Card -->
                <div class="lg:col-span-6">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <!-- Main Couple Image -->
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-stone-200 aspect-[3/4] max-h-[580px] w-full">
                            <img src="{{ asset('images/beranda/hero-couple.jpg') }}" 
                                 alt="Koleksi Batik Pasangan Hamzah Style" 
                                 class="w-full h-full object-cover object-top hover:scale-102 transition duration-700">

                            <!-- Floating Card at Bottom of Image -->
                            <div class="absolute bottom-6 left-6 right-6 bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl shadow-xl border border-stone-100 flex items-center justify-between">
                                <div class="pr-2">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-amber-800">
                                        Karya Unggulan
                                    </span>
                                    <h4 class="font-bold text-stone-900 text-sm sm:text-base mt-0.5 leading-snug">
                                        Sarimbit Parang Seling &amp; Motif Kawung
                                    </h4>
                                </div>
                                <a href="#produk-unggulan" 
                                   class="w-10 h-10 rounded-full bg-[#201A17] text-white flex items-center justify-center shrink-0 hover:bg-[#B58742] transition shadow-md"
                                   title="Lihat Koleksi Ini">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= SECTION: JELAJAHI KOLEKSI ================= -->
    <section id="koleksi" class="py-12 md:py-16 bg-white/60 border-t border-b border-[#EFE8DE] relative"
             x-data="{
                 canScrollLeft: false,
                 canScrollRight: true,
                 progress: 0,
                 isDown: false,
                 startX: 0,
                 scrollLeftPos: 0,
                 checkScroll() {
                     const el = this.$refs.slider;
                     if (!el) return;
                     const maxScroll = el.scrollWidth - el.clientWidth;
                     this.canScrollLeft = el.scrollLeft > 10;
                     this.canScrollRight = el.scrollLeft < maxScroll - 10;
                     this.progress = maxScroll > 0 ? Math.min(100, Math.max(0, (el.scrollLeft / maxScroll) * 100)) : 100;
                 },
                 scroll(dir) {
                     const el = this.$refs.slider;
                     if (!el) return;
                     const card = el.querySelector('.category-card');
                     const step = card ? (card.offsetWidth + 20) : 260;
                     el.scrollBy({ left: dir === 'left' ? -step : step, behavior: 'smooth' });
                 },
                 startDrag(e) {
                     this.isDown = true;
                     this.startX = e.pageX - this.$refs.slider.offsetLeft;
                     this.scrollLeftPos = this.$refs.slider.scrollLeft;
                 },
                 stopDrag() {
                     this.isDown = false;
                 },
                 onDrag(e) {
                     if (!this.isDown) return;
                     e.preventDefault();
                     const x = e.pageX - this.$refs.slider.offsetLeft;
                     const walk = (x - this.startX) * 1.4;
                     this.$refs.slider.scrollLeft = this.scrollLeftPos - walk;
                     this.checkScroll();
                 }
             }"
             x-init="$nextTick(() => checkScroll()); window.addEventListener('resize', () => checkScroll())">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header & Navigation Buttons -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-[#B58742] block mb-1">
                        WARISAN NUSANTARA
                    </span>
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-[#1F1916]">
                        Jelajahi Koleksi
                    </h2>
                    <p class="text-stone-600 text-xs sm:text-sm mt-1.5 max-w-xl leading-relaxed">
                        Menghadirkan pesona wastra nusantara dalam potongan tren masa kini.
                    </p>
                </div>

                <!-- Navigation Controls (Slider Buttons) -->
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-xs text-stone-400 font-medium hidden sm:inline-block">Geser koleksi</span>
                    <button type="button"
                            @click="scroll('left')"
                            :disabled="!canScrollLeft"
                            :class="!canScrollLeft ? 'opacity-35 cursor-not-allowed border-stone-200 text-stone-300' : 'hover:bg-[#201A17] hover:text-white hover:border-[#201A17] text-stone-800 border-stone-300 shadow-2xs'"
                            class="w-10 h-10 rounded-full border bg-white flex items-center justify-center transition duration-200"
                            aria-label="Geser ke kiri">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button type="button"
                            @click="scroll('right')"
                            :disabled="!canScrollRight"
                            :class="!canScrollRight ? 'opacity-35 cursor-not-allowed border-stone-200 text-stone-300' : 'hover:bg-[#201A17] hover:text-white hover:border-[#201A17] text-stone-800 border-stone-300 shadow-2xs'"
                            class="w-10 h-10 rounded-full border bg-white flex items-center justify-center transition duration-200"
                            aria-label="Geser ke kanan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Dynamic Collection Cards Carousel (Horizontally Scrollable) -->
            <div class="relative">
                <div x-ref="slider"
                     @scroll.passive="checkScroll()"
                     @mousedown="startDrag($event)"
                     @mouseleave="stopDrag()"
                     @mouseup="stopDrag()"
                     @mousemove="onDrag($event)"
                     class="flex gap-4 sm:gap-5 overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory pb-5 pt-1 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 cursor-grab active:cursor-grabbing">
                    @forelse($kategoris as $kat)
                        <div class="category-card w-[210px] sm:w-[230px] md:w-[250px] shrink-0 snap-start group bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between select-none">
                            <div>
                                <div class="relative aspect-[4/5] overflow-hidden bg-stone-100">
                                    <span class="absolute top-3 left-3 z-10 bg-white/90 backdrop-blur-md text-stone-900 text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full shadow-xs border border-white/60">
                                        {{ $kat->produks_count }} Produk
                                    </span>
                                    <img src="{{ $kat->gambar_url }}" 
                                         alt="{{ $kat->nama_kategori }}" 
                                         draggable="false"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out pointer-events-none">
                                </div>
                                <div class="p-4 sm:p-5">
                                    <h3 class="font-bold text-base sm:text-lg text-stone-900 mb-1.5 group-hover:text-amber-800 transition line-clamp-1">
                                        {{ $kat->nama_kategori }}
                                    </h3>
                                    <p class="text-stone-600 text-xs leading-relaxed line-clamp-2">
                                        {{ $kat->deskripsi ?: 'Koleksi busana dan kerajinan batik autentik karya pengrajin sentra lokal pilihan.' }}
                                    </p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0">
                                <a href="{{ route('koleksi.index', ['kategori' => $kat->nama_kategori]) }}" 
                                   class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-stone-900 group-hover:text-amber-800 group-hover:translate-x-1 transition-all">
                                    <span>Lihat Koleksi</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="w-full py-12 text-center text-stone-500 bg-white rounded-3xl border border-dashed border-stone-300">
                            Belum ada kategori yang ditambahkan.
                        </div>
                    @endforelse
                </div>

                <!-- Scroll Progress Bar Track & Hint -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-2 text-stone-400 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        <span class="text-[11px] font-medium">Geser untuk melihat kategori lainnya</span>
                    </div>
                    <div class="w-24 sm:w-32 h-1 bg-stone-200/80 rounded-full overflow-hidden">
                        <div class="h-full bg-[#B58742] rounded-full transition-all duration-200"
                             :style="'width: ' + Math.max(20, progress) + '%'"></div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= SECTION: PRODUK UNGGULAN ================= -->
    <section id="produk-unggulan" class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header and Filter Tabs -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-[#B58742] block mb-1">
                        PRODUK PILIHAN
                    </span>
                    <h2 class="font-serif-title text-3xl sm:text-4xl font-bold text-[#1F1916]">
                        Produk Unggulan
                    </h2>
                    <p class="text-stone-600 text-sm sm:text-base mt-2 max-w-xl">
                        Pilihan terbaik yang menggabungkan estetika tradisi dan fesyen modern.
                    </p>
                </div>

                <!-- Category Filter Buttons (Dynamic from Database) -->
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="activeCategory = 'Semua'" 
                            :class="activeCategory === 'Semua' ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition">
                        Semua
                    </button>
                    @foreach($kategoris as $kat)
                        <button @click="activeCategory = '{{ addslashes($kat->nama_kategori) }}'" 
                                :class="activeCategory === '{{ addslashes($kat->nama_kategori) }}' ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                                class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition">
                            {{ $kat->nama_kategori }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid (Dynamic from Database) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($produks->take(8) as $p)
                    <div x-show="activeCategory === 'Semua' || activeCategory === '{{ addslashes($p->category_name) }}'"
                         x-transition
                         class="bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group">
                        <a href="{{ route('produk.detail', ['id' => $p->id]) }}" class="relative aspect-square overflow-hidden bg-stone-100 block">
                            <span class="absolute top-3 left-3 z-10 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-xs {{ $p->status === 'Stok Menipis' ? 'bg-[#5C3D28] text-white' : 'bg-white/95 text-stone-800 border-stone-200' }}">
                                {{ $p->status === 'Stok Menipis' ? 'Stok Terbatas' : ($p->status === 'Habis' ? 'Habis' : 'Ready Stock') }}
                            </span>
                            <span class="absolute bottom-3 left-3 z-10 bg-[#201A17]/80 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ $p->category_name }}
                            </span>
                            <img src="{{ $p->gambar_url }}" 
                                 alt="{{ $p->nama }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Star Rating -->
                                <div class="flex items-center gap-1 mb-2">
                                    <span class="text-amber-500 text-xs">★</span>
                                    <span class="text-xs font-bold text-stone-800">5.0</span>
                                    <span class="text-[11px] text-stone-400">({{ 15 + (($p->id * 4) % 25) }})</span>
                                </div>
                                <!-- Title -->
                                <h4 class="font-bold text-stone-900 text-sm leading-snug line-clamp-2 mb-2 group-hover:text-amber-800 transition">
                                    <a href="{{ route('produk.detail', ['id' => $p->id]) }}">
                                        {{ $p->nama }}
                                    </a>
                                </h4>
                                <!-- Price -->
                                <p class="text-base font-extrabold text-stone-900 mb-4">
                                    Rp {{ number_format($p->harga, 0, ',', '.') }}
                                </p>
                            </div>
                            <a href="{{ route('produk.detail', ['id' => $p->id]) }}" 
                               class="w-full py-2.5 px-4 rounded-full bg-[#201A17] hover:bg-stone-800 text-white text-xs font-semibold transition duration-200 shadow-xs flex items-center justify-center gap-1.5 text-center">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- View All Link -->
            <div class="text-center mt-12">
                <a href="{{ route('koleksi.index') }}" 
                   class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#201A17] hover:bg-stone-800 text-[#E5C38E] text-sm font-semibold transition shadow-md group">
                    <span>Lihat Semua Produk ({{ $produks->count() }})</span>
                    <svg class="w-4 h-4 text-[#E5C38E] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>

        </div>
    </section>

    <!-- ================= SECTION: TENTANG HAMZAH STYLE OFFICIAL ================= -->
    <section id="tentang-kami" class="py-16 md:py-24 bg-[#FAF7F2] border-t border-[#ECE4D8]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-14 items-center">
                
                <!-- Left: 3-Image Collage with Floating Badge -->
                <div class="lg:col-span-6">
                    <div class="relative grid grid-cols-12 gap-3 sm:gap-4 items-center">
                        <!-- Tall Main Image -->
                        <div class="col-span-7 rounded-3xl overflow-hidden shadow-xl aspect-[3/4] bg-stone-200">
                            <img src="{{ asset('images/beranda/hero-couple.jpg') }}" 
                                 alt="Karya Batik Hamzah Style" 
                                 class="w-full h-full object-cover hover:scale-103 transition duration-500">
                        </div>

                        <!-- 2 Stacked Images -->
                        <div class="col-span-5 space-y-3 sm:space-y-4">
                            <div class="rounded-2xl overflow-hidden shadow-md aspect-square bg-stone-200">
                                <img src="{{ asset('images/beranda/folded-shirts.jpg') }}" 
                                     alt="Lipatan Kemeja Batik" 
                                     class="w-full h-full object-cover hover:scale-103 transition duration-500">
                            </div>
                            <div class="rounded-2xl overflow-hidden shadow-md aspect-square bg-stone-200">
                                <img src="{{ asset('images/beranda/bags-accessories.jpg') }}" 
                                     alt="Kriya Olahan Kain Sisa" 
                                     class="w-full h-full object-cover hover:scale-103 transition duration-500">
                            </div>
                        </div>

                        <!-- Floating Badge on bottom-left -->
                        <div class="absolute -bottom-4 left-4 bg-white/95 backdrop-blur-md px-4 py-3 rounded-2xl shadow-xl border border-stone-200/80 max-w-[240px]">
                            <span class="block text-xs font-extrabold text-stone-900 leading-tight">
                                100% Motif Asli
                            </span>
                            <span class="text-[11px] text-stone-500 leading-snug block mt-0.5">
                                Dari pengrajin batik tradisional di Solo &amp; Jogja
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Story, Philosophy & 3 Feature Cards -->
                <div class="lg:col-span-6 lg:pl-4">
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-[#B58742] mb-3">
                        <span>TENTANG KAMI</span>
                    </div>

                    <h2 class="font-serif-title text-3xl sm:text-4xl font-bold text-[#1F1916] leading-tight mb-2">
                        Tentang Hamzah Style Official
                    </h2>

                    <p class="text-sm sm:text-base font-semibold text-[#8C5E35] mb-4">
                        Melestarikan Budaya, Mengolah Potensi Limbah Menjadi Karya Nyata
                    </p>

                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-8">
                        Hamzah Style Official hadir sebagai wujud cinta dan dedikasi mendalam terhadap warisan wastra Nusantara. Kami menghadirkan busana batik autentik yang dirancang harmonis dengan sentuhan gaya modern kontemporer, sekaligus memelopori gerakan kriya beretika dengan mengolah kain sisa menjadi karya bernilai artistik tinggi dan ramah lingkungan.
                    </p>

                    <!-- 3 Feature Cards -->
                    <div class="space-y-3.5">
                        <!-- Card 1 -->
                        <div class="p-4 rounded-2xl bg-white border border-[#EBE3D6] shadow-2xs flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#FAF5ED] border border-[#EADBCC] text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-sm">Warisan Mahakarya Nusantara</h4>
                                <p class="text-xs text-stone-500 mt-0.5 leading-relaxed">
                                    Menjaga keaslian motif batik, ketelitian canting manual, dan teknik cap tradisional warisan leluhur.
                                </p>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="p-4 rounded-2xl bg-white border border-[#EBE3D6] shadow-2xs flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#FAF5ED] border border-[#EADBCC] text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-sm">Desain Modern Berkelanjutan</h4>
                                <p class="text-xs text-stone-500 mt-0.5 leading-relaxed">
                                    Mengolah kain sirkular upcycled bernilai estetika premium untuk gaya hidup ramah lingkungan.
                                </p>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="p-4 rounded-2xl bg-white border border-[#EBE3D6] shadow-2xs flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#FAF5ED] border border-[#EADBCC] text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-sm">Pemberdayaan Komunitas Pengrajin</h4>
                                <p class="text-xs text-stone-500 mt-0.5 leading-relaxed">
                                    Bermitra langsung dengan sentra perajin lokal di sentra batik Yogyakarta, Solo, &amp; Pekalongan.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ================= SECTION: CTA BANNER (TEMUKAN KOLEKSI FAVORITMU) ================= -->
    <section class="py-12 md:py-16 bg-[#FAF7F2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl overflow-hidden bg-[#201A17] text-white p-8 sm:p-12 md:p-16 text-center shadow-2xl border border-stone-800">
                <!-- Tag -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-[#E5C38E] text-[11px] font-bold uppercase tracking-widest mb-4">
                    <span>ESTETIKA BERKELANJUTAN</span>
                </div>

                <!-- Heading -->
                <h2 class="font-serif-title text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-4">
                    Temukan Koleksi Favoritmu
                </h2>

                <!-- Subtitle -->
                <p class="text-stone-300 text-xs sm:text-sm md:text-base max-w-2xl mx-auto leading-relaxed mb-8">
                    Belanja produk batik premium dan olahan kain sisa ramah lingkungan dari Hamzah Style Official. Tampil percaya diri seraya ikut menjaga kelestarian bumi dan kebudayaan Nusantara.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-4 mb-12">
                    <a href="{{ route('koleksi.index') }}" 
                       class="px-8 py-3.5 rounded-full bg-white text-stone-900 hover:bg-stone-100 font-bold text-sm transition shadow-lg">
                        Mulai Belanja
                    </a>
                    <a href="https://wa.me/6281234567890?text=Halo%20Hamzah%20Style%20Official,%20saya%20ingin%20konsultasi%20koleksi%20batik" 
                       target="_blank"
                       class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full border border-stone-600 hover:border-white text-white font-semibold text-sm transition bg-white/5 hover:bg-white/10">
                        <svg class="w-4 h-4 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Konsultasi Lebih Lanjut</span>
                    </a>
                </div>

                <!-- Feature Icons Footer in Banner -->
                <div class="pt-8 border-t border-stone-800 flex flex-wrap items-center justify-center gap-6 sm:gap-12 text-xs text-stone-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E5C38E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75h-7.5A2.25 2.25 0 004.5 9.75v5.25m12-7.5h3.75"/></svg>
                        <span>Pengiriman Seluruh Indonesia</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E5C38E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                        <span>Jaminan Produk Asli 100%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E5C38E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <span>Pembayaran Aman Terverifikasi</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <x-storefront-footer />

    <!-- ================= QUICK VIEW MODAL ================= -->
    <div x-cloak x-show="quickViewModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="quickViewModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="quickViewModal = false"
             class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="quickViewModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-stone-200">
                
                <!-- Close Button -->
                <button @click="quickViewModal = false" 
                        class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/80 hover:bg-stone-100 text-stone-600 flex items-center justify-center transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <template x-if="selectedProduct">
                    <div class="grid grid-cols-1 sm:grid-cols-2">
                        <!-- Product Image -->
                        <div class="relative aspect-square sm:aspect-auto bg-stone-100 overflow-hidden">
                            <img :src="selectedProduct.gambar" :alt="selectedProduct.nama" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 bg-[#201A17] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full" x-text="selectedProduct.kategori"></span>
                        </div>

                        <!-- Product Info -->
                        <div class="p-6 sm:p-8 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 text-xs text-amber-600 mb-2">
                                    <span>★</span>
                                    <span class="font-semibold text-stone-800" x-text="selectedProduct.rating"></span>
                                </div>
                                <h3 class="font-serif-title font-bold text-xl sm:text-2xl text-stone-900 mb-2" x-text="selectedProduct.nama"></h3>
                                <p class="text-2xl font-extrabold text-[#B58742] mb-4" x-text="selectedProduct.harga"></p>
                                
                                <div class="border-t border-b border-stone-100 py-3 mb-4 space-y-1.5 text-xs text-stone-600">
                                    <p><strong class="text-stone-900">Material:</strong> <span x-text="selectedProduct.material"></span></p>
                                    <p><strong class="text-stone-900">Garansi:</strong> 100% Batik Asli Berkualitas</p>
                                </div>

                                <p class="text-xs sm:text-sm text-stone-600 leading-relaxed mb-6" x-text="selectedProduct.deskripsi"></p>
                            </div>

                            <div class="space-y-2">
                                <a :href="'https://wa.me/6281234567890?text=Halo%20Hamzah%20Style,%20saya%20tertarik%20dengan%20produk%20' + encodeURIComponent(selectedProduct.nama) + '%20(' + encodeURIComponent(selectedProduct.harga) + ')'"
                                   target="_blank"
                                   class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    Pesan via WhatsApp
                                </a>
                                <button @click="quickViewModal = false; showToast('Produk ditambahkan ke keranjang belanja'); cartCount++"
                                        class="w-full py-2.5 px-4 rounded-xl border border-stone-300 hover:bg-stone-50 text-stone-800 font-semibold text-sm transition">
                                    + Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

</body>
</html>
