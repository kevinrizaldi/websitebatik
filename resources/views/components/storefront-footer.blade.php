<!-- ================= FOOTER ================= -->
<footer id="kontak" class="bg-[#FAF3EA] border-t border-[#ECE4D8] pt-14 pb-10 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 mb-10">
            <!-- Col 1: Brand Info (col-span-4) -->
            <div class="md:col-span-4 space-y-4">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="HAMZAH STYLE OFFICIAL" 
                         class="h-7 w-auto object-contain">
                </div>
                <p class="text-xs text-stone-600 leading-relaxed max-w-sm">
                    Batik Modern & Olahan Kain Sisa Berkelanjutan. Menjaga warisan tekstil Nusantara melalui inovasi kriya beretika dan potongan adibusana ramah lingkungan.
                </p>
                <div class="pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-stone-300/80 bg-white/60 text-stone-700 text-[10px] font-bold tracking-wider uppercase">
                        <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918"/>
                        </svg>
                        KARYA BERKELANJUTAN
                    </span>
                </div>
            </div>

            <!-- Col 2: Navigasi (col-span-2) -->
            <div class="md:col-span-2">
                <h4 class="font-bold text-stone-900 text-sm mb-3">Navigasi</h4>
                <ul class="space-y-2 text-xs text-stone-600">
                    <li><a href="{{ url('/') }}" class="hover:text-stone-900 transition">Home</a></li>
                    <li><a href="{{ url('/#tentang-kami') }}" class="hover:text-stone-900 transition">Tentang Kami</a></li>
                    <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-900 transition">Produk</a></li>
                    <li><a href="{{ url('/#koleksi') }}" class="hover:text-stone-900 transition">Kategori</a></li>
                    <li><a href="{{ url('/#kontak') }}" class="hover:text-stone-900 transition">Kontak</a></li>
                </ul>
            </div>

            <!-- Col 3: Saluran Penjualan Resmi (col-span-3) -->
            <div class="md:col-span-3">
                <h4 class="font-bold text-stone-900 text-sm mb-3">Saluran Penjualan Resmi</h4>
                <ul class="space-y-2.5 text-xs text-stone-600">
                    <li>
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:text-stone-900 transition">
                            <svg class="w-4 h-4 text-stone-700 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.592 2.654-.696c1.029.575 1.767.877 2.806.877 3.18 0 5.766-2.586 5.767-5.766.001-3.18-2.585-5.76-5.767-5.76zm3.377 8.21c-.14.394-.814.733-1.125.782-.311.049-.714.072-2.029-.446-1.677-.661-2.738-2.383-2.822-2.495-.084-.112-.68-1.027-.68-1.958s.484-1.39.656-1.577c.172-.187.375-.233.5-.233.125 0 .25.002.359.007.115.006.269-.044.421.322.157.377.534 1.306.581 1.401.047.095.078.206.016.33-.062.125-.094.203-.187.312-.093.109-.197.243-.281.326-.094.094-.192.196-.083.383.109.187.484.799 1.039 1.293.714.636 1.316.833 1.503.926.187.094.297.078.406-.047.109-.125.469-.547.594-.734.125-.187.25-.156.422-.094.172.062 1.094.516 1.281.609.188.094.313.141.359.219.047.078.047.453-.093.847z"/>
                            </svg>
                            <span>WhatsApp Business</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center gap-2 hover:text-stone-900 transition">
                            <svg class="w-4 h-4 text-stone-700 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.651V9.35m0 0a3.001 3.001 0 003.75-.614A2.993 2.993 0 009 9.35c.616-.054 1.2-.346 1.631-.814A3.002 3.002 0 0014.25 9.35c.616-.054 1.2-.346 1.631-.814A3.001 3.001 0 0019.5 9.35m-16.5 0L4.5 4.5h15l1.5 4.85"/>
                            </svg>
                            <span>Shopee Official</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center gap-2 hover:text-stone-900 transition">
                            <svg class="w-4 h-4 text-stone-700 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>TikTok Shop</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 4: Media Sosial & Buletin (col-span-3) -->
            <div class="md:col-span-3 space-y-3">
                <h4 class="font-bold text-stone-900 text-sm">Media Sosial & Buletin</h4>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Dapatkan akses awal untuk rilisan eksklusif dan seri upcycle terbatas.
                </p>
                <div class="flex items-center gap-2.5 pt-1">
                    <a href="#" class="w-8 h-8 rounded-full bg-white border border-stone-200/90 shadow-2xs flex items-center justify-center text-stone-700 hover:text-stone-950 hover:border-stone-300 transition" title="Instagram">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white border border-stone-200/90 shadow-2xs flex items-center justify-center text-stone-700 hover:text-stone-950 hover:border-stone-300 transition" title="Website">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white border border-stone-200/90 shadow-2xs flex items-center justify-center text-stone-700 hover:text-stone-950 hover:border-stone-300 transition" title="Pinterest / Threads">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345-.09.375-.291 1.199-.332 1.357-.053.211-.174.256-.402.154-1.498-.697-2.435-2.887-2.435-4.647 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Copyright Line -->
        <div class="pt-6 border-t border-[#ECE4D8] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
            <p>&copy; 2025 Hamzah Style Official. All rights reserved.</p>
            <p>Made with pride in Indonesia</p>
        </div>
    </div>
</footer>

