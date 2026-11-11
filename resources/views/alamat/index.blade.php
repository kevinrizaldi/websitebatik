<x-storefront-layout title="Daftar Alamat — Hamzah Style Official">
    <div class="min-h-screen bg-[#FAF7F2] py-8 sm:py-10"
         x-data="{
             addressModalOpen: false,
             modalMode: 'add',
             editingId: null,
             saving: false,
             formErrors: [],
             provinces: [],
             cities: [],
             loadingProvinces: false,
             loadingCities: false,
             addrForm: { label: '', penerima: '', phone: '', provinsi: '', kota: '', province_id: '', city_id: '', kodepos: '', lengkap: '', utama: false },
             addressList: [
                 @foreach($alamats as $alamat)
                 {
                     id: {{ $alamat->id }},
                     label: '{{ addslashes($alamat->label_alamat) }}',
                     penerima: '{{ addslashes($alamat->penerima) }}',
                     phone: '{{ addslashes($alamat->no_telepon) }}',
                     provinsi: '{{ addslashes($alamat->provinsi ?? '') }}',
                     kota: '{{ addslashes($alamat->kota) }}',
                     province_id: '{{ $alamat->province_id ?? '' }}',
                     city_id: '{{ $alamat->city_id ?? '' }}',
                     kodepos: '{{ addslashes($alamat->kode_pos ?? '') }}',
                     lengkap: '{{ addslashes($alamat->alamat_lengkap) }}',
                     utama: {{ $alamat->is_utama ? 'true' : 'false' }}
                 }@if(!$loop->last),@endif
                 @endforeach
             ],
             async loadProvinces() {
                 if (this.provinces.length > 0) return;
                 this.loadingProvinces = true;
                 try {
                     const res = await fetch('{{ route('rajaongkir.provinces') }}');
                     const data = await res.json();
                     if (data.success && Array.isArray(data.provinces)) {
                         this.provinces = data.provinces;
                     }
                 } catch (e) {
                     console.error('Failed loading provinces', e);
                 } finally {
                     this.loadingProvinces = false;
                 }
             },
             async loadCities(provinceId) {
                 if (!provinceId) {
                     this.cities = [];
                     return;
                 }
                 this.loadingCities = true;
                 try {
                     const res = await fetch('{{ route('rajaongkir.cities') }}?province_id=' + provinceId);
                     const data = await res.json();
                     if (data.success && Array.isArray(data.cities)) {
                         this.cities = data.cities;
                     }
                 } catch (e) {
                     console.error('Failed loading cities', e);
                 } finally {
                     this.loadingCities = false;
                 }
             },
             openAdd() {
                 this.modalMode = 'add';
                 this.editingId = null;
                 this.formErrors = [];
                 this.addrForm = { label: '', penerima: '{{ addslashes($user->name) }}', phone: '{{ addslashes($user->phone ?? '') }}', provinsi: '', kota: '', province_id: '', city_id: '', kodepos: '', lengkap: '', utama: this.addressList.length === 0 };
                 this.loadProvinces();
                 this.addressModalOpen = true;
             },
             openEdit(id) {
                 const a = this.addressList.find(x => x.id === id);
                 if (!a) return;
                 this.modalMode = 'edit';
                 this.editingId = id;
                 this.formErrors = [];
                 this.addrForm = {
                     label: a.label,
                     penerima: a.penerima,
                     phone: a.phone,
                     provinsi: a.provinsi,
                     kota: a.kota,
                     province_id: a.province_id ? String(a.province_id) : '',
                     city_id: a.city_id ? String(a.city_id) : '',
                     kodepos: a.kodepos,
                     lengkap: a.lengkap,
                     utama: a.utama
                 };
                 this.loadProvinces().then(() => {
                     if (a.province_id) this.loadCities(a.province_id);
                 });
                 this.addressModalOpen = true;
             },
             onProvinceChange() {
                 const selected = this.provinces.find(p => String(p.province_id) === String(this.addrForm.province_id));
                 this.addrForm.provinsi = selected ? selected.province : '';
                 this.addrForm.city_id = '';
                 this.addrForm.kota = '';
                 if (this.addrForm.province_id) {
                     this.loadCities(this.addrForm.province_id);
                 } else {
                     this.cities = [];
                 }
             },
             onCityChange() {
                 const selected = this.cities.find(c => String(c.city_id) === String(this.addrForm.city_id));
                 if (selected) {
                     this.addrForm.kota = (selected.type ? selected.type + ' ' : '') + selected.city_name;
                     if (selected.postal_code && !this.addrForm.kodepos) {
                         this.addrForm.kodepos = selected.postal_code;
                     }
                 } else {
                     this.addrForm.kota = '';
                 }
             },
             submitModalForm() {
                 this.formErrors = [];
                 const p = (this.addrForm.penerima || '').trim();
                 const ph = (this.addrForm.phone || '').trim();
                 const l = (this.addrForm.lengkap || '').trim();
                 const provId = this.addrForm.province_id;
                 const cityId = this.addrForm.city_id;

                 if (!p) this.formErrors.push('Nama penerima wajib diisi.');
                 if (!ph) {
                     this.formErrors.push('Nomor telepon wajib diisi.');
                 } else if (ph.length < 10 || ph.length > 15) {
                     this.formErrors.push('Nomor telepon harus antara 10 - 15 digit.');
                 }
                 if (!provId) this.formErrors.push('Provinsi wajib dipilih.');
                 if (!cityId) this.formErrors.push('Kabupaten/Kota wajib dipilih.');
                 if (!l) this.formErrors.push('Alamat lengkap wajib diisi.');

                 if (this.formErrors.length > 0) return;

                 this.saving = true;
                 const isEdit = this.modalMode === 'edit' && this.editingId;
                 const url = isEdit ? '{{ url('/alamat') }}/' + this.editingId : '{{ route('alamat.store') }}';
                 const method = isEdit ? 'PUT' : 'POST';

                 fetch(url, {
                     method: method,
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         label_alamat: this.addrForm.label || 'Rumah',
                         penerima: this.addrForm.penerima,
                         no_telepon: this.addrForm.phone,
                         provinsi: this.addrForm.provinsi,
                         kota: this.addrForm.kota,
                         province_id: this.addrForm.province_id ? parseInt(this.addrForm.province_id) : null,
                         city_id: this.addrForm.city_id ? parseInt(this.addrForm.city_id) : null,
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
         }">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">
                {{-- Kolom Kiri: Sidebar Profil & Navigasi --}}
                <div class="lg:col-span-4">
                    <x-customer-sidebar active="alamat" :user="$user" :alamatCount="$alamats->count()" :pesananAktif="$pesananAktif" />
                </div>

                {{-- Kolom Kanan: Daftar Alamat Tersimpan --}}
                <div class="space-y-6 lg:col-span-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">Daftar Alamat</h1>
                            <p class="mt-1 text-xs text-stone-500 sm:text-sm">Kelola buku alamat untuk kemudahan pengiriman pesanan Anda.</p>
                        </div>
                        <button @click="openAdd()" type="button" class="inline-flex items-center gap-2 rounded-xl bg-[#201A17] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>Tambah Alamat Baru</span>
                        </button>
                    </div>

                    {{-- Daftar Alamat Tersimpan --}}
                    <div class="space-y-4">
                        @forelse ($alamats as $alamat)
                            <div class="rounded-3xl border {{ $alamat->is_utama ? 'border-[#201A17] bg-[#FAF3EA]' : 'border-[#ECE4D8] bg-white' }} p-6 shadow-xs transition hover:border-[#DECDB8]">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <h3 class="font-bold text-stone-900 text-base">{{ $alamat->label_alamat }}</h3>
                                        @if ($alamat->is_utama)
                                            <span class="rounded-full bg-[#201A17] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Utama</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-2 text-sm font-semibold text-stone-800">{{ $alamat->penerima }}</p>
                                <p class="text-xs text-stone-500 mt-0.5">{{ $alamat->no_telepon }}</p>
                                <p class="mt-2 text-xs sm:text-sm leading-relaxed text-stone-600">{{ $alamat->alamat_lengkap }}{{ $alamat->kota ? ', '.$alamat->kota : '' }}{{ $alamat->provinsi ? ', '.$alamat->provinsi : '' }}{{ $alamat->kode_pos ? ' '.$alamat->kode_pos : '' }}</p>

                                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-stone-200/60 pt-3">
                                    <button @click="openEdit({{ $alamat->id }})" type="button" class="rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-semibold text-stone-800 transition hover:bg-[#EFE3D2]">Ubah</button>
                                    <form action="{{ route('alamat.destroy', $alamat) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50">Hapus</button>
                                    </form>
                                    @if (! $alamat->is_utama)
                                        <form action="{{ route('alamat.setDefault', $alamat) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-xl bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-700 transition hover:bg-stone-200">Jadikan Utama</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-[#ECE4D8] bg-white p-12 text-center shadow-xs">
                                <p class="font-bold text-stone-900 text-sm">Belum ada alamat tersimpan</p>
                                <p class="mt-1 text-xs text-stone-500">Tambahkan alamat pertama lewat tombol Tambah Alamat Baru.</p>
                                <button @click="openAdd()" type="button" class="mt-4 rounded-xl bg-[#201A17] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-stone-800">+ Tambah Alamat</button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MODAL TAMBAH / UBAH ALAMAT ================= --}}
        <div x-cloak x-show="addressModalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="addressModalOpen = false"></div>

            <div class="relative w-full max-w-lg rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900" x-text="modalMode === 'add' ? 'Tambah Alamat Baru' : 'Ubah Alamat'">Tambah Alamat Baru</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Lengkapi data alamat untuk pengiriman pesanan.</p>
                    </div>
                    <button @click="addressModalOpen = false" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 max-h-[70vh] overflow-y-auto pr-1">
                    <div x-show="formErrors.length > 0" class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                        <template x-for="(err, i) in formErrors" :key="i">
                            <p x-text="'• ' + err"></p>
                        </template>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Nama Penerima <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="addrForm.penerima" placeholder="Nama penerima" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Nomor Telepon <span class="text-rose-500">*</span></label>
                            <input type="tel" x-model="addrForm.phone" placeholder="08123456789" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Label Alamat</label>
                        <input type="text" x-model="addrForm.label" placeholder="Rumah, Kantor, dll" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Provinsi <span class="text-rose-500">*</span></label>
                            <select x-model="addrForm.province_id" @change="onProvinceChange()" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                <option value="" x-text="loadingProvinces ? 'Memuat provinsi...' : '-- Pilih Provinsi --'">-- Pilih Provinsi --</option>
                                <template x-for="p in provinces" :key="p.province_id">
                                    <option :value="p.province_id" x-text="p.province" :selected="String(p.province_id) === String(addrForm.province_id)"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Kabupaten/Kota <span class="text-rose-500">*</span></label>
                            <select x-model="addrForm.city_id" @change="onCityChange()" :disabled="!addrForm.province_id || loadingCities" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none disabled:opacity-50">
                                <option value="" x-text="loadingCities ? 'Memuat kota...' : (addrForm.province_id ? '-- Pilih Kota --' : '-- Pilih Provinsi Dulu --')">-- Pilih Kota --</option>
                                <template x-for="c in cities" :key="c.city_id">
                                    <option :value="c.city_id" x-text="(c.type ? c.type + ' ' : '') + c.city_name" :selected="String(c.city_id) === String(addrForm.city_id)"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Kode Pos</label>
                        <input type="text" x-model="addrForm.kodepos" placeholder="Contoh: 12190" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-400">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <textarea x-model="addrForm.lengkap" rows="3" placeholder="Nama jalan, Nomor rumah, RT/RW, Patokan" class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-xs text-stone-700 pt-1">
                        <input type="checkbox" x-model="addrForm.utama" class="h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-0">
                        <span>Jadikan sebagai Alamat Utama</span>
                    </label>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-stone-100 pt-3">
                    <button @click="addressModalOpen = false" type="button" class="rounded-xl px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Batal</button>
                    <button @click="submitModalForm()" type="button" :disabled="saving" class="rounded-xl bg-[#201A17] px-6 py-2.5 text-xs font-semibold text-white hover:bg-stone-800 disabled:opacity-50">
                        <span x-text="saving ? 'Menyimpan...' : (modalMode === 'add' ? 'Simpan Alamat Baru' : 'Simpan Perubahan')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-storefront-layout>
