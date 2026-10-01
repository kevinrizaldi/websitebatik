<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Alamat Saya - Hamzah Style Official</title>
    <meta name="description" content="Kelola daftar alamat pengiriman pelanggan Hamzah Style Official.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF7F2; color: #26211D; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen bg-[#FAF7F2] text-[#26211D] antialiased selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          toastMessage: '{{ session('success') ?? '' }}',
          addressModalOpen: false,
          modalMode: 'add',
          editingId: null,
          saving: false,
          formErrors: [],
          addrForm: { label: '', penerima: '', phone: '', provinsi: '', kota: '', kodepos: '', lengkap: '', utama: false },
          addressList: [
              @foreach($alamats as $alamat)
              {
                  id: {{ $alamat->id }},
                  label: '{{ addslashes($alamat->label_alamat) }}',
                  penerima: '{{ addslashes($alamat->penerima) }}',
                  phone: '{{ addslashes($alamat->no_telepon) }}',
                  provinsi: '{{ addslashes($alamat->provinsi ?? '') }}',
                  kota: '{{ addslashes($alamat->kota) }}',
                  kodepos: '{{ addslashes($alamat->kode_pos ?? '') }}',
                  lengkap: '{{ addslashes($alamat->alamat_lengkap) }}',
                  utama: {{ $alamat->is_utama ? 'true' : 'false' }}
              }@if(!$loop->last),@endif
              @endforeach
          ],
          openAdd() {
              this.modalMode = 'add';
              this.editingId = null;
              this.formErrors = [];
              this.addrForm = { label: '', penerima: '', phone: '', provinsi: '', kota: '', kodepos: '', lengkap: '', utama: this.addressList.length === 0 };
              this.addressModalOpen = true;
          },
          openEdit(id) {
              const found = this.addressList.find(a => a.id === id);
              if (!found) return;
              this.modalMode = 'edit';
              this.editingId = id;
              this.formErrors = [];
              this.addrForm = { label: found.label, penerima: found.penerima, phone: found.phone, provinsi: found.provinsi, kota: found.kota, kodepos: found.kodepos, lengkap: found.lengkap, utama: found.utama };
              this.addressModalOpen = true;
          },
          submitModalForm() {
              this.saving = true;
              this.formErrors = [];
              const isAdd = this.modalMode === 'add';
              const url = isAdd ? '{{ route('alamat.store') }}' : '{{ url('/alamat') }}/' + this.editingId;
              fetch(url, {
                  method: isAdd ? 'POST' : 'PUT',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      label_alamat: this.addrForm.label,
                      penerima: this.addrForm.penerima,
                      no_telepon: this.addrForm.phone,
                      provinsi: this.addrForm.provinsi,
                      kota: this.addrForm.kota,
                      kode_pos: this.addrForm.kodepos,
                      alamat_lengkap: this.addrForm.lengkap,
                      is_utama: !!this.addrForm.utama
                  })
              })
              .then(async res => {
                  const data = await res.json().catch(() => ({}));
                  this.saving = false;
                  if (res.ok && data.success) {
                      window.location.reload();
                  } else if (data.errors) {
                      this.formErrors = Object.values(data.errors).flat();
                  } else {
                      this.formErrors = [data.message || 'Gagal menyimpan alamat.'];
                  }
              })
              .catch(() => {
                  this.saving = false;
                  this.formErrors = ['Gagal menghubungi server.'];
              });
          }
      }"
      x-init="if (toastMessage) { setTimeout(() => { toastMessage = ''; }, 3500); }">

    <div x-cloak x-show="toastMessage" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-stone-700 bg-[#1F1916] px-5 py-3 text-white shadow-xl">
        <svg class="h-5 w-5 shrink-0 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>

    <header class="sticky top-0 z-40 border-b border-[#ECE4D8] bg-[#FAF7F2]/90 backdrop-blur-md">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="group flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#201A17] text-[#E5C38E] shadow-sm transition group-hover:bg-stone-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" /></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-sans text-sm font-bold uppercase tracking-wider text-stone-900 sm:text-base">HAMZAH STYLE</span>
                    <span class="-mt-1 text-[10px] font-medium uppercase tracking-widest text-stone-500">OFFICIAL</span>
                </div>
            </a>
            <nav class="hidden items-center gap-8 md:flex">
                <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Home</a>
                <a href="{{ route('koleksi.index') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Produk</a>
                <a href="{{ route('pesanan.index') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Pesanan</a>
                <a href="{{ route('profile.edit') }}" class="border-b-2 border-stone-900 pb-0.5 text-sm font-medium text-stone-900">Akun</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('keranjang.index') }}" class="relative rounded-full p-2 text-stone-600 transition hover:bg-stone-200/50 hover:text-stone-900" title="Keranjang Belanja">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <span class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#B58742] text-[10px] font-bold text-white" x-text="cartCount">{{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }}</span>
                </a>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="rounded-full p-2 text-stone-600 hover:bg-stone-200/50 md:hidden"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
        </div>
        <div x-cloak x-show="mobileMenuOpen" class="border-t border-[#ECE4D8] bg-[#FAF7F2] px-4 py-3 md:hidden">
            <div class="flex flex-col gap-1 text-sm font-medium">
                <a href="{{ url('/') }}" class="rounded-lg px-3 py-2 hover:bg-stone-200/50">Home</a>
                <a href="{{ route('koleksi.index') }}" class="rounded-lg px-3 py-2 hover:bg-stone-200/50">Produk</a>
                <a href="{{ route('pesanan.index') }}" class="rounded-lg px-3 py-2 hover:bg-stone-200/50">Pesanan</a>
                <a href="{{ route('profile.edit') }}" class="rounded-lg bg-stone-900 px-3 py-2 text-white">Akun Saya</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <p class="text-xs text-stone-500">
            <a href="{{ url('/') }}" class="hover:text-stone-900">Beranda</a>
            <span class="mx-1">›</span>
            <a href="{{ route('profile.edit') }}" class="hover:text-stone-900">Profil</a>
            <span class="mx-1">›</span>
            <span class="font-semibold text-stone-800">Daftar Alamat</span>
        </p>
        <p class="mt-4 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-stone-500">
            <span class="h-1.5 w-1.5 rounded-full bg-[#8C2F1B]"></span> Pengaturan Alamat Pengiriman
        </p>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <h1 class="font-serif-title text-3xl font-extrabold text-stone-900 sm:text-4xl">Daftar Alamat Saya</h1>
            <button @click="openAdd()" type="button" class="rounded-full bg-[#201A17] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-stone-800">+ Isi Alamat Baru</button>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Kolom kiri: profil mini + navigasi --}}
            <div class="space-y-5 lg:col-span-3">
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#201A17] text-lg font-extrabold text-[#E5C38E]">
                        {{ strtoupper(substr($user->name, 0, 1)).strtoupper(substr(strrchr($user->name, ' ') ?: '', 1, 1)) }}
                    </div>
                    <h2 class="mt-3 font-bold text-stone-900">{{ $user->name }}</h2>
                    <p class="text-xs text-stone-500">{{ $user->email }}</p>
                </div>
                <nav class="rounded-3xl border border-[#ECE4D8] bg-white p-3 shadow-sm">
                    <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100"><span>Data Diri</span></a>
                    <a href="{{ route('alamat.index') }}" class="flex items-center justify-between rounded-2xl bg-[#F5EDE1] px-4 py-3 text-sm font-bold text-stone-900"><span>Daftar Alamat</span><span class="h-1.5 w-1.5 rounded-full bg-[#201A17]"></span></a>
                    <a href="{{ route('pesanan.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100"><span>Pesanan Saya</span><span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $pesananAktif }} Aktif</span></a>
                    <a href="{{ route('pesanan.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100"><span>Ulasan Saya</span><span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $ulasanCount }}</span></a>
                    <div class="my-2 border-t border-stone-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center rounded-2xl px-4 py-3 text-left text-sm font-bold text-red-700 transition hover:bg-red-50">Keluar dari Akun</button>
                    </form>
                </nav>
            </div>

            {{-- Daftar alamat tersimpan --}}
            <div class="space-y-4 lg:col-span-9">
                <h2 class="flex items-center gap-2 font-bold text-stone-900">Alamat Tersimpan ({{ $alamats->count() }} Alamat)</h2>
                @forelse ($alamats as $alamat)
                    <div class="rounded-3xl border {{ $alamat->is_utama ? 'border-[#201A17] bg-[#FBF3EA]' : 'border-[#ECE4D8] bg-white' }} p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-bold text-stone-900">{{ $alamat->label_alamat }}</h3>
                            @if ($alamat->is_utama)
                                <span class="rounded bg-[#201A17] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Utama</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm font-semibold text-stone-800">{{ $alamat->penerima }}</p>
                        <p class="text-sm text-stone-600">{{ $alamat->no_telepon }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-stone-600">{{ $alamat->alamat_lengkap }}{{ $alamat->kota ? ', '.$alamat->kota : '' }}{{ $alamat->provinsi ? ', '.$alamat->provinsi : '' }}{{ $alamat->kode_pos ? ' '.$alamat->kode_pos : '' }}</p>
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-stone-200/70 pt-3">
                            <button @click="openEdit({{ $alamat->id }})" type="button" class="rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-bold text-stone-800 transition hover:bg-[#EFE3D2]">Ubah</button>
                            <form action="{{ route('alamat.destroy', $alamat) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                            </form>
                            @if (! $alamat->is_utama)
                                <form action="{{ route('alamat.setDefault', $alamat) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-xl bg-stone-200/70 px-3 py-2 text-xs font-bold text-stone-700 transition hover:bg-stone-300">Jadikan Utama</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-8 text-center">
                        <p class="font-bold text-stone-900">Belum ada alamat tersimpan</p>
                        <p class="mt-1 text-xs text-stone-500">Tambahkan alamat pertama lewat tombol + Isi Alamat Baru.</p>
                        <button @click="openAdd()" type="button" class="mt-4 rounded-xl bg-[#201A17] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-stone-800">+ Tambah Alamat</button>
                    </div>
                @endforelse
            </div>
        </div>
    </main>

    <!-- ================= MODAL TAMBAH / UBAH ALAMAT ================= -->
    <div x-cloak x-show="addressModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="addressModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="addressModalOpen = false"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="addressModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-stone-200">

                <div class="px-6 py-5 bg-[#FAF7F2] border-b border-[#ECE4D8] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-stone-900" x-text="modalMode === 'add' ? 'Tambah Alamat Baru' : 'Ubah Alamat'">Tambah Alamat Baru</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Field sesuai data alamat (nama, telepon, kota, alamat lengkap).</p>
                    </div>
                    <button @click="addressModalOpen = false" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-3 max-h-[70vh] overflow-y-auto">
                    <div x-show="formErrors.length > 0" class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                        <template x-for="(err, i) in formErrors" :key="i">
                            <p x-text="'• ' + err"></p>
                        </template>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Nama Penerima <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="addrForm.penerima" @input="addrForm.penerima = addrForm.penerima.replace(/[^a-zA-Z\s\'.]/g, '')" placeholder="Masukkan nama penerima" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Nomor Telepon <span class="text-rose-500">*</span></label>
                            <input type="tel" inputmode="numeric" maxlength="15" x-model="addrForm.phone" @input="addrForm.phone = addrForm.phone.replace(/[^0-9]/g, '')" placeholder="Contoh: 08123456789 (10-15 digit)" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Label Alamat</label>
                        <input type="text" x-model="addrForm.label" @input="addrForm.label = addrForm.label.replace(/[^a-zA-Z0-9\s]/g, '')" placeholder="Contoh: Rumah, Kantor" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Provinsi</label>
                            <input type="text" x-model="addrForm.provinsi" @input="addrForm.provinsi = addrForm.provinsi.replace(/[^a-zA-Z\s]/g, '')" placeholder="Provinsi" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Kabupaten/Kota <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="addrForm.kota" @input="addrForm.kota = addrForm.kota.replace(/[^a-zA-Z\s]/g, '')" placeholder="Kabupaten/Kota" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Kode Pos</label>
                        <input type="text" inputmode="numeric" maxlength="5" x-model="addrForm.kodepos" @input="addrForm.kodepos = addrForm.kodepos.replace(/[^0-9]/g, '')" placeholder="Contoh: 12190 (5 digit)" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <textarea x-model="addrForm.lengkap" @input="addrForm.lengkap = addrForm.lengkap.replace(/[^a-zA-Z0-9\s.,\/\-]/g, '')" rows="3" placeholder="Nama jalan, Gedung, No. Rumah, RT/RW, dan patokan" class="w-full rounded-xl border border-stone-200 bg-[#FDF9F3] px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-xs text-stone-600">
                        <input type="checkbox" x-model="addrForm.utama" class="h-4 w-4 rounded border-stone-300">
                        <span>Jadikan ini sebagai Alamat Utama</span>
                    </label>
                </div>

                <div class="px-6 py-4 bg-stone-50 border-t border-stone-200 flex items-center justify-between">
                    <button @click="addressModalOpen = false" type="button" class="px-4 py-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">Batal</button>
                    <button @click="submitModalForm()" type="button" :disabled="saving" class="px-6 py-2.5 text-xs font-bold text-white bg-[#201A17] hover:bg-stone-800 disabled:opacity-50 rounded-xl transition shadow-md">
                        <span x-text="saving ? 'Menyimpan...' : (modalMode === 'add' ? 'Simpan Alamat Baru' : 'Simpan Perubahan')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="border-t border-[#ECE4D8] bg-[#FAF7F2] py-8 text-xs text-stone-600">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
            <div class="flex items-center gap-2"><span class="font-bold uppercase text-stone-900">HAMZAH STYLE OFFICIAL</span><span>&bull;</span><span>E-Commerce Batik &amp; Olahan Kain Sisa</span></div>
            <p>&copy; {{ date('Y') }} Hamzah Style Official. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
